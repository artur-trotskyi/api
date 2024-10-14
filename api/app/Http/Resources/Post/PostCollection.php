<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\BaseCollection;

class PostCollection extends BaseCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray($request): array
    {
        $transformedPosts = [];
        foreach ($this->resource['items'] as $post) {
            $transformedPosts[] = new PostResource($post);
        }

        $data = [
            'posts' => $transformedPosts,
            'totalPages' => $this->resource['totalPages'],
            'totalItems' => $this->resource['totalItems'],
            'items' => count($transformedPosts),
            'page' => $this->resource['page'],
        ];

        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $data,
        ];
    }
}
