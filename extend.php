<?php

namespace ClarkWinkelmann\JWTCookieLogin;

use ClarkWinkelmann\JWTCookieLogin\Middleware\AuthenticateWithJWT;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Extend;
use Flarum\Http\Middleware\AuthenticateWithSession;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__ . '/js/dist/forum.js'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__ . '/js/dist/admin.js'),

    new Extend\Locales(__DIR__ . '/resources/locale'),

    (new Extend\Routes('api'))
        ->patch('/jwt/users/{id}', 'jwt-cookie-login.users.update', Controller\EditUserController::class),

    (new Extend\Middleware('forum'))
        ->insertAfter(AuthenticateWithSession::class, AuthenticateWithJWT::class),
    (new Extend\Middleware('admin'))
        ->insertAfter(AuthenticateWithSession::class, AuthenticateWithJWT::class),
    (new Extend\Middleware('api'))
        ->insertAfter(AuthenticateWithSession::class, AuthenticateWithJWT::class),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attributes(ForumAttributes::class),
];
