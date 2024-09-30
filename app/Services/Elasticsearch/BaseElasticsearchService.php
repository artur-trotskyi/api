<?php

namespace App\Services\Elasticsearch;

use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Exception;

class BaseElasticsearchService
{
    /**
     * Repository.
     *
     * @var object
     */
    public object $repo;

    /**
     * Check if an index exists in Elasticsearch.
     *
     * @param string $index
     * @return bool
     * @throws ClientResponseException If a client error occurs.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ServerResponseException If a server error occurs.
     * @throws Exception If an unexpected error occurs.
     */
    public function indexExists(string $index): bool
    {
        return $this->repo->indexExists($index);
    }

    /**
     * Creates an index in Elasticsearch with the specified mappings.
     *
     * @param string $index The name of the index to create.
     * @return bool The response from the Elasticsearch client.
     * @throws ClientResponseException If a client error occurs.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ServerResponseException If a server error occurs.
     * @throws Exception If an unexpected error occurs.
     */
    public function createIndex(string $index): bool
    {
        return $this->repo->createIndex($index);
    }

    /**
     * Check the connection to Elasticsearch by pinging the server.
     *
     * @return bool True if Elasticsearch is available, false otherwise.
     * @throws ClientResponseException If a client error occurs.
     * @throws ServerResponseException If a server error occurs.
     */
    public function checkConnection(): bool
    {
        return $this->repo->checkConnection();
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
        return $this->repo->deleteAllDocuments($index);
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
        return $this->repo->deleteDocument($index, $id);
    }

    /**
     * Perform bulk indexing of documents in Elasticsearch.
     *
     * @param array $bulkData The bulk data to be indexed.
     * @return bool True if the bulk indexing was successful, false otherwise.
     */
    public function bulkIndexDocuments(array $bulkData): bool
    {
        return $this->repo->bulkIndexDocuments($bulkData);
    }
}
