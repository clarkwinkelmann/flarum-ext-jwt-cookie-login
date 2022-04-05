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
        if (!$serializer->getRequest()->getAttribute('jwtStatelessAuth')) {
            return [];
        }

        $logoutRedirect = $this->settings->get('jwt-cookie-login.logoutRedirect');

        return [
            // Use an explicit "false" value when URL is disabled because we'll use this value to know the logout field should be hidden
            'logoutRedirect' => $logoutRedirect ?: false,
        ];
    }
}
