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
     * @param  Post  $model  Repo DB ORM Model
     */
    public function __construct(Post $model)
    {
        parent::__construct($model);
    }

    public function getFilteredWithPaginate(
        ?string $q, int $itemsPerPage, int $page, array $strictFilters, ?string $sortBy = null, ?string $orderBy = null): LengthAwarePaginator
    {
        $query = $this->model
            ->when($q, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('content', 'like', '%' . $search . '%');
                });
            })
            ->when(! empty($strictFilters['title']), function ($query) use ($strictFilters): void {
                $query->where('title', $strictFilters['title']);
            })
            ->when(! empty($strictFilters['content']), function ($query) use ($strictFilters): void {
                $query->where('content', $strictFilters['content']);
            });

        if ($sortBy && $orderBy) {
            $query->orderBy($sortBy, $orderBy);
        }
        $query->orderBy($this->sortBy, $this->sortOrder);

        /*if ($itemsPerPage === -1) {
            $itemsPerPage = $query->count();
        }*/

        return $query->paginate($itemsPerPage, ['*'], 'page', $page);
    }
}
