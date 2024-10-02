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
    protected Model $model;
    protected Client $elasticsearch;

    /**
     * Repository Constructor.
     *
     * @param Model $model Repo DB ORM Model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->elasticsearch = app(Client::class);
    }

    /**
     * Search for items in Elasticsearch based on the given query.
     *
     * @param string|null $query The search query.
     * @param int $itemsPerPage
     * @param int $page
     * @param array $strictFilters
     * @param array $fields
     * @return array A collection of search results.
     */
    public function search(string|null $query, int $itemsPerPage, int $page, array $strictFilters, array $fields = []): array
    {
        return $this->searchOnElasticsearch($query, $itemsPerPage, $page, $strictFilters, $fields);
    }

    /**
     * Build a collection from the raw search results.
     *
     * @param array $items The raw search results from Elasticsearch.
     * @return Collection A collection of Eloquent models.
     */
    protected function buildCollection(array $items): Collection
    {
        $ids = Arr::pluck($items['hits']['hits'], '_id');

        return $this->model::findMany($ids)->sortBy(function ($item) use ($ids) {
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
     * Delete a document from the specified index by its ID.
     *
     * @param string $index The name of the Elasticsearch index.
     * @param string $id The ID of the document to delete.
     * @return bool True on success, false on failure.
     * @throws Exception If an unexpected error occurs.
     */
    public function deleteDocument(string $index, string $id): bool
    {
        try {
            $response = $this->elasticsearch->delete([
                'index' => $index,
                'id' => $id,
            ]);

            return $response->getStatusCode() === Response::HTTP_OK;

        } catch (ClientResponseException|ServerResponseException|MissingParameterException|NoNodeAvailableException $e) {
            Log::error("Error deleting document with ID {$id} from index {$index}: " . $e->getMessage());
            return false;
        } catch (Exception $e) {
            Log::error("Unexpected error when deleting document with ID {$id} from index {$index}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform the search on Elasticsearch for any model.
     *
     * @param string|null $query The search query.
     * @param int $itemsPerPage
     * @param int $page
     * @param array $strictFilters
     * @param array $fields Fields to search within.
     * @return array The raw search results from Elasticsearch.
     */
    protected function searchOnElasticsearch(string|null $query, int $itemsPerPage, int $page, array $strictFilters, array $fields): array
    {
        try {
            $from = ($page - 1) * $itemsPerPage;

            // Forming an array for the query
            $boolQuery = [];

            // If there are strict filters, add them to `must`
            if (!empty($strictFilters)) {
                foreach ($strictFilters as $field => $value) {
                    if (!empty($value)) {
                        $boolQuery['must'][] = [
                            'match_phrase' => [
                                // Use .keyword for an exact match
                                "{$field}.keyword" => $value,
                            ],
                        ];
                    }
                }
            }

            // If there are query and fields, add multi_match
            if (!empty($query) && !empty($fields)) {
                $boolQuery['must'][] = [
                    'multi_match' => [
                        'query' => $query,
                        'fields' => $fields,
                    ],
                ];
            }

            // If there are no filters or query, use match_all
            if (empty($boolQuery)) {
                $boolQuery['must'][] = [
                    'match_all' => (object)[],
                ];
            }

            // Sending a request to Elasticsearch
            $response = $this->elasticsearch->search([
                'index' => $this->model::getSearchIndex(),
                'type' => '_doc',
                'body' => [
                    'from' => $from,
                    'size' => $itemsPerPage,
                    'query' => [
                        'bool' => $boolQuery,
                    ],
                ],
            ]);

            $data = $response->asArray();

            // Generate results with pagination
            $totalItems = $data['hits']['total']['value'] ?? 0;
            $items = array_map(function ($hit) {
                return $hit['_source'];
            }, $data['hits']['hits']);

            return [
                'items' => $items,
                'totalPages' => $totalItems > 0 ? (int)ceil($totalItems / $itemsPerPage) : 0,
                'totalItems' => $totalItems,
                'page' => $page
            ];

        } catch (NoNodeAvailableException|ClientResponseException|ServerResponseException $e) {
            Log::error('Elasticsearch search error: ' . $e->getMessage(), ['exception' => $e]);
            return [];
        } catch (Exception $e) {
            Log::error('General search error: ' . $e->getMessage(), ['exception' => $e]);
            return [];
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
