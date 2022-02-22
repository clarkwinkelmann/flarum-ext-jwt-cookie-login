<?php

namespace ClarkWinkelmann\JWTCookieLogin\Controller;

use Flarum\Api\Controller\UpdateUserController;
use Flarum\Api\Serializer\CurrentUserSerializer;
use Flarum\Http\RequestUtil;
use Flarum\User\Command\EditUser;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class EditUserController extends UpdateUserController
{
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $subject = Arr::get($request->getQueryParams(), 'id');
        $actor = RequestUtil::getActor($request);
        $data = Arr::get($request->getParsedBody(), 'data', []);

        if ($actor->jwt_subject === $subject) {
            $this->serializer = CurrentUserSerializer::class;
        }

        // TODO: the code could be refactored to remove this additional SQL query
        // but since it's meant for automated tasks performance doesn't matter too much
        $userId = optional(User::query()->where('jwt_subject', $subject)->first('id'))->id;

        return $this->bus->dispatch(
            new EditUser($userId, $actor, $data)
        );
    }
}
