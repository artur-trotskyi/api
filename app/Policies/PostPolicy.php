<?php

namespace App\Policies;

use App\Constants\AppConstants;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PostPolicy
{
    public function modify(User $user, Post $post): Response
    {
        if ($user->getAttribute('id') !== $post->getAttribute('user_id')) {
            throw new AccessDeniedHttpException(AppConstants::EXCEPTION_MESSAGES['authorization_for_data']);
        }

        return Response::allow();
    }
}
