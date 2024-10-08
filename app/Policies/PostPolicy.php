<?php

namespace App\Policies;

use App\Enums\ExceptionMessagesEnum;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostPolicy
{
    public function modify(User $user, Post $post): Response
    {
        if ($user->getAttribute('id') !== $post->getAttribute('user_id')) {
            throw new AccessDeniedHttpException(ExceptionMessagesEnum::AuthorizationForData->message());
        }

        return Response::allow();
    }
}
