<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    /**
     * @throws AuthorizationException
     */
    public function modify(User $user, Post $post): Response
    {
        if ($user->id !== $post->user_id) {
            throw new AuthorizationException('You do not own this post');
        }

        return Response::allow();
    }
}
