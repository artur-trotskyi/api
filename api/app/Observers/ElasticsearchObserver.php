<?php

namespace App\Observers;

use Elastic\Elasticsearch\Client;

class ElasticsearchObserver
{
    private Client $elasticsearchClient;

    public function __construct(Client $elasticsearchClient)
    {
        $this->elasticsearchClient = $elasticsearchClient;
    }

    /**
     * Handle the model's saved event.
     *
     * @param  mixed  $model  The model that was saved.
     */
    public function saved(mixed $model): void
    {
        $model->elasticSearchIndex($this->elasticsearchClient);
    }

    /**
     * Handle the model's deleted event.
     *
     * @param  mixed  $model  The model that was deleted.
     */
    public function deleted(mixed $model): void
    {
        $model->elasticSearchDelete($this->elasticsearchClient);
    }
}
