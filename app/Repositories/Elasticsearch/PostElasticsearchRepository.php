<?php

namespace App\Repositories\Elasticsearch;

use App\Models\Post;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
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
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(Model $model, string $query = ''): Collection
    {
        return parent::search(new Post(), $query);
    }

    /**
     * Retrieves the mappings for the index.
     *
     * @return array The mappings for the index.
     */
    protected function getMappings(): array
    {
        return [
            'properties' => [
                'user_id' => ['type' => 'keyword'],
                'title' => ['type' => 'text'],
                'content' => ['type' => 'text'],
                'tags' => ['type' => 'keyword'],
            ],
        ];
    }
}
