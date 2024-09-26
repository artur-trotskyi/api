<?php

namespace App\Repositories\Elasticsearch;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PostElasticsearchRepository extends BaseElasticsearchRepository
{
    /**
     * Search for Post items in Elasticsearch based on the given query.
     *
     * @param Model $model The Eloquent model to perform the search on.
     * @param string $query The search query.
     * @return Collection A collection of search results.
     */
    public function search(Model $model, string $query = ''): Collection
    {
        return parent::search(new Post(), $query);
    }
}
