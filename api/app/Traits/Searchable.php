<?php

namespace App\Traits;

use App\Observers\ElasticsearchObserver;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\MissingParameterException;
use Elastic\Elasticsearch\Exception\ServerResponseException;

trait Searchable
{
    /**
     * Convert the model to an array suitable for indexing in Elasticsearch.
     */
    abstract public function toElasticsearchDocumentArray(): array;

    /**
     * Get the name of the Elasticsearch index associated with the model.
     *
     * @return string The name of the index.
     */
    abstract public static function getSearchIndex(): string;

    /**
     * Boot the Searchable trait for the model.
     * Observes the ElasticsearchObserver if search is enabled in configuration.
     */
    public static function bootSearchable(): void
    {
        static::observe(ElasticsearchObserver::class);
    }

    /**
     * Index the model in Elasticsearch.
     *
     * @param  Client  $elasticsearchClient  The Elasticsearch client instance.
     *
     * @throws ClientResponseException If there is a client error during indexing.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ServerResponseException If there is a server error during indexing.
     */
    public function elasticsearchIndex(Client $elasticsearchClient): void
    {
        $elasticsearchClient->index([
            'index' => $this->getTable(),
            'id' => $this->getKey(),
            'body' => $this->toElasticsearchDocumentArray(),
        ]);
    }

    /**
     * Delete the model from Elasticsearch.
     *
     * @param  Client  $elasticsearchClient  The Elasticsearch client instance.
     *
     * @throws ServerResponseException If there is a server error during deletion.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ClientResponseException If there is a client error during deletion.
     */
    public function elasticsearchDelete(Client $elasticsearchClient): void
    {
        $elasticsearchClient->delete([
            'index' => $this->getTable(),
            'id' => $this->getKey(),
        ]);
    }
}
