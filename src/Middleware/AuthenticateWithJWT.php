<?php

namespace ClarkWinkelmann\JWTCookieLogin\Middleware;

use Dflydev\FigCookies\FigRequestCookies;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Flarum\Foundation\Config;
use Flarum\Http\RequestUtil;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Command\RegisterUser;
use Flarum\User\User;
use GuzzleHttp\Client;
use GuzzleHttp\Utils;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class AuthenticateWithJWT implements MiddlewareInterface
{
    protected $settings;
    protected $cache;
    protected $client;
    protected $config;

    public function __construct(SettingsRepositoryInterface $settings, Repository $cache, Client $client, Config $config)
    {
        $this->settings = $settings;
        $this->cache = $cache;
        $this->client = $client;
        $this->config = $config;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $actor = $this->getUser($request);

        if ($actor) {
            $actor->updateLastSeen()->save();

            $request = RequestUtil::withActor($request, $actor);
            $request = $request->withAttribute('bypassCsrfToken', true);
            // Removing session might break frontend
            //$request = $request->withoutAttribute('session');
        }

        return $handler->handle($request);
    }

    protected function getUser(ServerRequestInterface $request): ?User
    {
        $cookie = FigRequestCookies::get($request, $this->settings->get('jwt-cookie-login.cookieName') ?: 'invalid');

        $jwt = $cookie->getValue();

        if (empty($jwt)) {
            $this->logInDebugMode('No JWT cookie');
            return null;
        }

        try {
            $payload = JWT::decode($jwt, $this->keys());
        } catch (\Exception $exception) {
            $this->logInDebugMode('Invalid JWT cookie');
            return null;
        }

        $audience = $this->settings->get('jwt-cookie-login.audience');

        if ($audience && (!isset($payload->aud) || $payload->aud !== $audience)) {
            $this->logInDebugMode('Invalid JWT audience (' . ($payload->aud ?? 'missing') . ')');
            return null;
        }

        $user = User::query()->where('jwt_subject', $payload->sub)->first();

        if ($user) {
            $this->logInDebugMode('Authenticating existing JWT user [' . $user->jwt_subject . ' / ' . $user->id . ']');

            return $user;
        }

        $registerPayload = [
            'attributes' => [
                'isEmailConfirmed' => true,
                'password' => Str::random(32),
            ],
        ];

        if ($registrationHook = $this->settings->get('jwt-cookie-login.registrationHook')) {
            $response = $this->client->post($this->replaceStringParameters($registrationHook, $payload), [
                'headers' => [
                    'Authorization' => 'Token ' . $jwt,
                ],
            ]);

            $registerPayload = array_merge_recursive(
                $registerPayload,
                Arr::get(Utils::jsonDecode($response->getBody()->getContents(), true), 'data', [])
            );
        }

        if (
            !Arr::has($registerPayload, 'attributes.username') &&
            $usernameTemplate = $this->settings->get('jwt-cookie-login.usernameTemplate')
        ) {
            $registerPayload['attributes']['username'] = $this->replaceStringParameters($usernameTemplate, $payload);
        }

        if (
            !Arr::has($registerPayload, 'attributes.email') &&
            $emailTemplate = $this->settings->get('jwt-cookie-login.emailTemplate')
        ) {
            $registerPayload['attributes']['email'] = $this->replaceStringParameters($emailTemplate, $payload);
        }

        // TODO: make user configurable
        $actor = User::query()->where('id', 1)->firstOrFail();

        /**
         * @var $bus Dispatcher
         */
        $bus = resolve(Dispatcher::class);

        $user = $bus->dispatch(new RegisterUser($actor, $registerPayload));

        // TODO: move to user edit listener
        $user->jwt_subject = $payload->sub;
        $user->save();

        $this->logInDebugMode('Authenticating new JWT user [' . $user->jwt_subject . ' / ' . $user->id . ']');

        return $user;
    }

    protected function keys()
    {
        if ($this->settings->get('jwt-cookie-login.publicKey')) {
            return new Key($this->settings->get('jwt-cookie-login.publicKey'), $this->settings->get('jwt-cookie-login.publicKeyAlgorithm'));
        }

        $keys = $this->cache->remember('jwt-cookie-login.firebaseKeys', 86400, function () {
            // Based on https://firebase.google.com/docs/auth/admin/verify-id-tokens?hl=en#verify_id_tokens_using_a_third-party_jwt_library
            return Utils::jsonDecode($this->client->get('https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com')->getBody()->getContents(), true);
        });

        return array_map(function ($key) {
            return new Key($key, 'RS256');
        }, $keys);
    }

    protected function replaceStringParameters(string $string, $payload): string
    {
        return preg_replace_callback('~{([a-zA-Z0-9_-]+)}~', function ($matches) use ($payload) {
            if (!isset($payload->{$matches[1]})) {
                throw new \Exception('Replacement pattern {' . $matches[1] . '} was not found in JWT payload');
            }

            return $payload->{$matches[1]};
        }, $string);
    }

    protected function logInDebugMode(string $message)
    {
        if ($this->config->inDebugMode()) {
            /**
             * @var $logger LoggerInterface
             */
            $logger = resolve(LoggerInterface::class);
            $logger->info($message);
        }
    }
}
