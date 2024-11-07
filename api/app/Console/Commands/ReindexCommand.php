<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\Elasticsearch\PostElasticsearchService;
use Exception;
use Illuminate\Console\Command;

class ReindexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search:reindex';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Indexes all Posts to Elasticsearch';

    /**
     * Execute the console command.
     */
    public function handle(PostElasticsearchService $elasticsearchService): int
    {
        try {
            // Check the connection to Elasticsearch
            if (! $elasticsearchService->checkConnection()) {
                $this->error('Could not connect to Elasticsearch.');

                return self::FAILURE;
            }

            // Check if the index exists and create it if necessary
            $searchIndex = Post::getSearchIndex();
            if (! $elasticsearchService->indexExists($searchIndex)) {
                $isIndexCreated = $elasticsearchService->createIndex($searchIndex);
                if (! $isIndexCreated) {
                    $this->error('Failed to create index.');

                    return self::FAILURE;
                }
                $this->info('Created Posts index.');
            }

            // Deleted all documents in the Posts index.
            if (! $elasticsearchService->deleteAllDocuments($searchIndex)) {
                $this->error('Failed to delete all documents in the Posts index.');

                return self::FAILURE;
            }
            $this->info('Deleted all documents in the Posts index.');

            // Use bulk indexing for better performance and perform bulk indexing
            $this->info('Indexing all Posts. This might take a while...');
            $bulkData = $elasticsearchService->prepareBulkData($searchIndex);
            if (! empty($bulkData)) {
                if (! $elasticsearchService->bulkIndexDocuments($bulkData)) {
                    $this->error('Failed to perform bulk indexing.');

                    return self::FAILURE;
                }
                $this->info('All Posts have been indexed successfully!');
            }

        } catch (Exception $e) {
            $this->error('Unexpected error: ' . $e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
