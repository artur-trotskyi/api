<?php

namespace App\Repositories\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Elastic\Transport\Exception\NoNodeAvailableException;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseElasticsearchRepository
{
    protected Client $elasticsearch;

    /**
     * Repository Constructor.
     *
     * @param Client $elasticsearch The Elasticsearch client instance.
     */
    public function __construct
    (
        Client $elasticsearch
    )
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
            'index' => $model::getSearchIndex(),
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

    /**
     * Check if an index exists in Elasticsearch.
     *
     * @param string $index The name of the index to check.
     * @return bool True if the index exists, false otherwise.
     */
    public function indexExists(string $index): bool
    {
        try {
            $exists = $this->elasticsearch->indices()->exists(['index' => $index]);

            return $exists->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|MissingParameterException|ServerResponseException|NoNodeAvailableException $e) {
            Log::error("Error occurred when checking index existence '{$index}': " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Unexpected error occurred when checking index existence '{$index}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Creates an index in Elasticsearch with the specified mappings.
     *
     * @param string $index The name of the index to create.
     * @return bool True if the index was created successfully, false otherwise.
     */
    public function createIndex(string $index): bool
    {
        try {
            $response = $this->elasticsearch->indices()->create([
                'index' => $index,
                'body' => [
                    'mappings' => $this->getMappings(),
                ],
            ]);

            return $response->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|MissingParameterException|ServerResponseException|NoNodeAvailableException $e) {
            Log::error("Error occurred when creating index '{$index}': " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Unexpected error occurred when creating index '{$index}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check the connection to Elasticsearch by pinging the server.
     *
     * @return bool True if Elasticsearch is available, false otherwise.
     */
    public function checkConnection(): bool
    {
        try {
            $response = $this->elasticsearch->ping();

            return $response->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|ServerResponseException|NoNodeAvailableException $e) {
            Log::error('Error occurred while pinging Elasticsearch: ' . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error('Unexpected error occurred while pinging Elasticsearch: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete all documents from the specified index.
     *
     * @param string $index
     * @return bool
     * @throws Exception If an unexpected error occurs.
     */
    public function deleteAllDocuments(string $index): bool
    {
        try {
            $response = $this->elasticsearch->deleteByQuery([
                'index' => $index,
                'body' => [
                    'query' => [
                        'match_all' => (object)[],
                    ],
                ],
            ]);

            return $response->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|ServerResponseException|MissingParameterException|NoNodeAvailableException $e) {
            Log::error("Error deleting documents from index {$index}: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Unexpected error when deleting documents from index {$index}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform bulk indexing of documents in Elasticsearch.
     *
     * @param array $bulkData The bulk data to be indexed.
     * @return bool True if the bulk indexing was successful, false otherwise.
     */
    public function bulkIndexDocuments(array $bulkData): bool
    {
        try {
            $response = $this->elasticsearch->bulk($bulkData);

            return $response->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|NoNodeAvailableException|ServerResponseException $e) {
            Log::error("Error during bulk indexing: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Unexpected error during bulk indexing: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves the mappings for the index.
     *
     * @return array The mappings for the index.
     */
    abstract protected function getMappings(): array;
}
