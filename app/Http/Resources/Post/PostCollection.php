<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\BaseCollection;

class PostCollection extends BaseCollection
{
    public static $wrap = null;

    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
