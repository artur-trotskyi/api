<?php

namespace App\Dto;

class PostStoreDto
{
    public string $user_id;
    public string $title;
    public string $content;
    public string $tags;

    /**
     * PostFilterDto constructor.
     *
     * @param array $data An associative array with data for store posts.
     */
    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'];
        $this->title = $data['title'];
        $this->content = $data['content'];
        $this->tags = $data['tags'];
    }
}
