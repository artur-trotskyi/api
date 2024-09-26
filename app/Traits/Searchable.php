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
     * Boot the Searchable trait for the model.
     * Observes the ElasticsearchObserver if search is enabled in configuration.
     *
     * @return void
     */
    public static function bootSearchable(): void
    {
        if (config('services.search.enabled')) {
            static::observe(ElasticsearchObserver::class);
        }
    }

    /**
     * Index the model in Elasticsearch.
     *
     * @param Client $elasticsearchClient The Elasticsearch client instance.
     * @return void
     * @throws ClientResponseException If there is a client error during indexing.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ServerResponseException If there is a server error during indexing.
     */
    public function elasticsearchIndex(Client $elasticsearchClient): void
    {
        $elasticsearchClient->index([
            'index' => $this->getTable(),
            'type' => '_doc',
            'id' => $this->getKey(),
            'body' => $this->toElasticsearchDocumentArray(),
        ]);
    }

    /**
     * Delete the model from Elasticsearch.
     *
     * @param Client $elasticsearchClient The Elasticsearch client instance.
     * @return void
     * @throws ServerResponseException If there is a server error during deletion.
     * @throws MissingParameterException If a required parameter is missing.
     * @throws ClientResponseException If there is a client error during deletion.
     */
    public function elasticsearchDelete(Client $elasticsearchClient): void
    {
        $elasticsearchClient->delete([
            'index' => $this->getTable(),
            'type' => '_doc',
            'id' => $this->getKey(),
        ]);
    }

    /**
     * Convert the model to an array suitable for indexing in Elasticsearch.
     *
     * @return array
     */
    abstract public function toElasticsearchDocumentArray(): array;


}

