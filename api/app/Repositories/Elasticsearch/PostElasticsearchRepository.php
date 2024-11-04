<?php

namespace App\Repositories\Elasticsearch;

use App\Models\Post;

class PostElasticsearchRepository extends BaseElasticsearchRepository
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

    /**
     * Perform the search on Elasticsearch for Post model.
     *
     * @param  string|null  $query  The search query.
     * @param  array  $fields  Fields to search within.
     * @return array The raw search results from Elasticsearch.
     */
    protected function searchOnElasticsearch(?string $query, int $itemsPerPage, int $page, array $strictFilters, ?string $sortBy, ?string $orderBy, array $fields): array
    {
        return parent::searchOnElasticsearch($query, $itemsPerPage, $page, $strictFilters, $sortBy, $orderBy, ['title^5', 'content', 'tags']);
    }
}
