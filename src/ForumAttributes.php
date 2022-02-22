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
        return [
            'logoutRedirect' => $this->settings->get('jwt-cookie-login.logoutRedirect'),
        ];
    }
}
