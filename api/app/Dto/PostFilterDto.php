<?php

namespace App\Dto;

class PostFilterDto
{
    public ?string $q;
    public int $itemsPerPage;
    public int $page;
    public ?string $title;
    public ?string $content;
    public ?string $tags;
    public ?string $sortBy;
    public ?string $orderBy;

    /**
     * PostFilterDto constructor.
     *
     * @param array $data An associative array with data for filtering posts.
     */
    public function __construct(array $data)
    {
        $this->q = $data['q'];
        $this->itemsPerPage = $data['itemsPerPage'];
        $this->page = $data['page'];
        $this->title = $data['title'] ?? null;
        $this->content = $data['content'] ?? null;
        $this->tags = $data['tags'] ?? null;
        $this->sortBy = $data['sortBy'] ?? null;
        $this->orderBy = $data['orderBy'] ?? null;
    }
}
