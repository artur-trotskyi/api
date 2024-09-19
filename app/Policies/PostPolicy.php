<?php

namespace App\Policies;

use App\Exceptions\ApiException;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * @throws ApiException
     */
    public function modify(User $user, Post $post): Response
    {
        if ($user->id !== $post->user_id) {
            throw new ApiException(
                new AuthorizationException('You do not own this post.'),
                'You do not own this post.',
                \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
            );
        }

        return Response::allow();
    }
}
