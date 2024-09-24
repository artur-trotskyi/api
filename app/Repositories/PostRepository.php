<?php

namespace App\Repositories;

use App\Models\Post;
use App\Repositories\Interfaces\PostRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

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

    /**
     * @param string|null $q
     * @param int $itemsPerPage
     * @param int $page
     * @param string|null $title
     * @param string|null $content
     * @param string|null $sortBy
     * @param string|null $orderBy
     * @return LengthAwarePaginator
     */
    public function getFilteredWithPaginate(
        string $q = null, int $itemsPerPage = -1, int $page = 1, string $title = null, string $content = null,
        string $sortBy = null, string $orderBy = null): LengthAwarePaginator
    {
        $query = $this->model
            ->when($q, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->when($title, function ($query, $search) {
                $query->where('title', $search);
            })
            ->when($content, function ($query, $search) {
                $query->where('content', $search);
            });

        if ($sortBy && $orderBy) {
            $query->orderBy($sortBy, $orderBy);
        }
        $query->orderBy($this->sortBy, $this->sortOrder);

        if ($itemsPerPage === -1) {
            $itemsPerPage = $query->count();
        }

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }
}
