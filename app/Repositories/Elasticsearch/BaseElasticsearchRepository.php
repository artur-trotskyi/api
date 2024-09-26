<?php

namespace App\Repositories\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

abstract class BaseElasticsearchRepository
{
    protected Client $elasticsearch;

    /**
     * Repository Constructor.
     *
     * @param Client $elasticsearch The Elasticsearch client instance.
     */
    public function __construct(Client $elasticsearch)
    {
        $this->elasticsearch = $elasticsearch;
    }

    /**
     * Search for items in Elasticsearch based on the given query.
     *
     * @param Model $model The Eloquent model to perform the search on.
     * @param string $query The search query.
     * @return Collection A collection of search results.
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    public function search(Model $model, string $query = ''): Collection
    {
        $items = $this->searchOnElasticsearch($model, $query);
        return $this->buildCollection($model, $items);
    }

    /**
     * Perform the search on Elasticsearch.
     *
     * @param Model $model The Eloquent model to perform the search on.
     * @param string $query The search query.
     * @return array The raw search results from Elasticsearch.
     * @throws ClientResponseException
     * @throws ServerResponseException
     */
    protected function searchOnElasticsearch(Model $model, string $query = ''): array
    {
        $response = $this->elasticsearch->search([
            'index' => $model->getSearchIndex(),
            'type' => '_doc',
            'body' => [
                'query' => [
                    'multi_match' => [
                        'fields' => ['title^5', 'content', 'tags'],
                        'query' => $query,
                    ],
                ],
            ],
        ]);

        return $response->asArray();
    }

    /**
     * Build a collection from the raw search results.
     *
     * @param Model $model The Eloquent model to find results for.
     * @param array $items The raw search results from Elasticsearch.
     * @return Collection A collection of Eloquent models.
     */
    protected function buildCollection(Model $model, array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        return $model::findMany($ids)->sortBy(function ($item) use ($ids) {
            return array_search($item->getKey(), $ids);
        });
    }
}
