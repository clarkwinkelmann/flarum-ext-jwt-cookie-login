<?php

namespace ClarkWinkelmann\JWTCookieLogin\Tests\integration;

use Dflydev\FigCookies\Cookie;
use Dflydev\FigCookies\FigRequestCookies;
use Firebase\JWT\JWT;
use Flarum\Testing\integration\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AccessTest extends TestCase
{
    const COOKIE_NAME = 'test_jwt';
    const JWT_KEY = '0cfcdc8f24b128c6acc8ad030b8a34ba';
    const JWT_ALGO = 'HS256';
    const JWT_SUB1 = 'abcd';
    const JWT_SUB2 = 'bcde';
    const JWT_AUD = 'machine.local';

    protected function setUp(): void
    {
        parent::setUp();

        $this->setting('jwt-cookie-login.cookieName', self::COOKIE_NAME);
        $this->setting('jwt-cookie-login.publicKey', self::JWT_KEY);
        $this->setting('jwt-cookie-login.publicKeyAlgorithm', self::JWT_ALGO);
        $this->setting('jwt-cookie-login.audience', self::JWT_AUD);

        $this->extension('clarkwinkelmann-jwt-cookie-login');

        $this->prepareDatabase([
            'users' => [
                [
                    'id' => 2,
                    'username' => 'tester',
                    'password' => '',
                    'email' => 'tester@machine.local',
                    'is_email_confirmed' => 1,
                    'jwt_subject' => self::JWT_SUB1,
                ],
                [
                    'id' => 3,
                    'username' => 'tester2',
                    'password' => '',
                    'email' => 'tester2@machine.local',
                    'is_email_confirmed' => 1,
                    'jwt_subject' => self::JWT_SUB2,
                ],
            ],
        ]);
    }

    protected function createDiscussionRequest(): ServerRequestInterface
    {
        return $this->request('POST', '/api/discussions', [
            'json' => [
                'data' => [
                    'attributes' => [
                        'title' => 'Test',
                        'content' => 'Hello World',
                    ],
                ],
            ],
        ]);
    }

    protected function addJWTCookie(ServerRequestInterface $request, string $sub, string $aud, string $key): ServerRequestInterface
    {
        return FigRequestCookies::set($request, new Cookie(self::COOKIE_NAME, JWT::encode([
            'sub' => $sub,
            'aud' => $aud,
        ], $key, self::JWT_ALGO)));
    }

    protected function addValidJWTCookie(ServerRequestInterface $request): ServerRequestInterface
    {
        return $this->addJWTCookie($request, self::JWT_SUB1, self::JWT_AUD, self::JWT_KEY);
    }

    protected function requestWithCookiesAndCsrfHeaderFrom(ServerRequestInterface $request, ResponseInterface $previous): ServerRequestInterface
    {
        $request = $this->requestWithCookiesFrom($request, $previous);

        return $request->withHeader('X-CSRF-Token', $previous->getHeaderLine('X-CSRF-Token'));
    }

    public function test_normal_valid()
    {
        $homepageResponse = $this->send($this->addValidJWTCookie($this->request('GET', '/')));

        $this->assertEquals(201, $this->send($this->addValidJWTCookie($this->requestWithCookiesAndCsrfHeaderFrom($this->createDiscussionRequest(), $homepageResponse)))->getStatusCode());
    }

    public function test_no_csrf()
    {
        // Without sending the session cookie at all
        $this->assertEquals(400, $this->send($this->addValidJWTCookie($this->createDiscussionRequest()))->getStatusCode());
    }

    public function test_guest_csrf()
    {
        $homepageResponse = $this->send($this->request('GET', '/'));

        // Sending a session cookie from a guest session
        $this->assertEquals(400, $this->send($this->addValidJWTCookie($this->requestWithCookiesAndCsrfHeaderFrom($this->createDiscussionRequest(), $homepageResponse)))->getStatusCode());
    }

    public function test_user_change_csrf()
    {
        $homepageResponse = $this->send($this->addJWTCookie($this->request('GET', '/'), self::JWT_SUB2, self::JWT_AUD, self::JWT_KEY));

        // Sending a session cookie from a different valid JWT user
        $this->assertEquals(400, $this->send($this->addValidJWTCookie($this->requestWithCookiesAndCsrfHeaderFrom($this->createDiscussionRequest(), $homepageResponse)))->getStatusCode());
    }

    public function test_bad_audience()
    {
        $homepageResponse = $this->send($this->addValidJWTCookie($this->request('GET', '/')));

        $this->assertEquals(403, $this->send($this->addJWTCookie($this->requestWithCookiesAndCsrfHeaderFrom($this->createDiscussionRequest(), $homepageResponse), self::JWT_SUB1, 'badaudience', self::JWT_KEY))->getStatusCode());
    }

    public function test_bad_key()
    {
        $homepageResponse = $this->send($this->addValidJWTCookie($this->request('GET', '/')));

        $this->assertEquals(403, $this->send($this->addJWTCookie($this->requestWithCookiesAndCsrfHeaderFrom($this->createDiscussionRequest(), $homepageResponse), self::JWT_SUB1, self::JWT_AUD, 'badkey'))->getStatusCode());
    }
}
