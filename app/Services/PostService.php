<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Cache;

class PostService extends BaseService
{
    public function __construct
    (
        PostRepository $repo
    )
    {
        $this->repo = $repo;
    }

    /**
     * @param string|null $q
     * @param int $itemsPerPage
     * @param int $page
     * @param string|null $title
     * @param string|null $content
     * @param string|null $sortBy
     * @param string|null $orderBy
     * @return array
     */
    public function filter(
        string|null $q, int $itemsPerPage, int $page, string|null $title, string|null $content, string|null $sortBy, string|null $orderBy): array
    {
        $cacheTag = config('cache.tags.users');
        $cacheKey = "q={$q}&itemsPerPage={$itemsPerPage}&page={$page}&title={$title}&content={$content}&sortBy={$sortBy}&orderBy={$orderBy}";
        $posts = Cache::tags($cacheTag)->remember($cacheKey, config('cache.ttl'), function () use ($q, $itemsPerPage, $page, $title, $content, $sortBy, $orderBy) {
            return $this->repo->getFilteredWithPaginate($q, $itemsPerPage, $page, $title, $content, $sortBy, $orderBy);
        });

        return [
            'items' => $posts->items(),
            'totalPages' => $posts->total() === 0 ? 0 : $posts->lastPage(),
            'totalItems' => $posts->total(),
            'page' => $posts->currentPage()
        ];
    }
}
