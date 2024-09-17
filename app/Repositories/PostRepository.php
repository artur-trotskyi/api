<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;

class PostRepository extends BaseRepository implements PostRepositoryInterface
{
    /**
     * Repo Constructor
     * Override to clarify typehinted model.
     *
     * @param Post $model Repo DB ORM Model
     */
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }
}
