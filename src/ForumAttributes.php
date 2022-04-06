<?php

namespace ClarkWinkelmann\JWTCookieLogin;

use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Settings\SettingsRepositoryInterface;

class ForumAttributes
{
    protected $settings;

    public function __construct(SettingsRepositoryInterface $settings)
    {
        $this->settings = $settings;
    }

    public function __invoke(ForumSerializer $serializer): array
    {
        $attributes = [];

        $iframe = $this->settings->get('jwt-cookie-login.hiddenIframe');

        if ($iframe) {
            $attributes['jwtIframe'] = $iframe;

            $autoLogin = $this->settings->get('jwt-cookie-login.autoLoginDelay');

            // Defaults to 2000ms
            $attributes['autoLoginDelay'] = is_numeric($autoLogin) ? (int)$autoLogin : 2000;
        }

        if (!$serializer->getRequest()->getAttribute('jwtStatelessAuth')) {
            return $attributes;
        }

        $logoutRedirect = $this->settings->get('jwt-cookie-login.logoutRedirect');

        return $attributes + [
                // Use an explicit "false" value when URL is disabled because we'll use this value to know the logout field should be hidden
                'logoutRedirect' => $logoutRedirect ?: false,
            ];
    }
}
