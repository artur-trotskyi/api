<?php

namespace App\Http\Resources\Post;

use App\Http\Resources\BaseResource;
use Illuminate\Http\Request;

class PostResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
