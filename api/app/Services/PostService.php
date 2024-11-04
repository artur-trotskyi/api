<?php

namespace App\Services;

use App\Repositories\PostRepository;
use Illuminate\Support\Facades\Cache;

class PostService extends BaseService
{
    /**
     * Create a new PostService instance.
     *
     * @param  PostRepository  $repo  The repository for managing posts.
     */
    public function __construct(PostRepository $repo)
    {
        $this->repo = $repo;
    }

    /**
     * Filter and paginate posts based on various criteria.
     */
    public function filter(
        ?string $q, int $itemsPerPage, int $page, array $strictFilters, ?string $sortBy, ?string $orderBy): array
    {
        $cacheTag = config('cache.tags.users');
        $filtersQueryString = http_build_query($strictFilters);
        $cacheKey = "q={$q}&itemsPerPage={$itemsPerPage}&page={$page}&{$filtersQueryString}&sortBy={$sortBy}&orderBy={$orderBy}";
        $posts = Cache::tags($cacheTag)->remember($cacheKey, config('cache.ttl'), function () use ($q, $itemsPerPage, $page, $strictFilters, $sortBy, $orderBy) {
            return $this->repo->getFilteredWithPaginate($q, $itemsPerPage, $page, $strictFilters, $sortBy, $orderBy);
        });

        return [
            'items' => $posts->items(),
            'totalPages' => $posts->total() === 0 ? 0 : $posts->lastPage(),
            'totalItems' => $posts->total(),
            'page' => $posts->currentPage(),
        ];
    }
}
