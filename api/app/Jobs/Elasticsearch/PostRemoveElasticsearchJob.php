<?php

namespace App\Jobs\Elasticsearch;

use App\Models\Post;
use App\Services\Elasticsearch\PostElasticsearchService;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class PostRemoveElasticsearchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $postId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $postId)
    {
        $this->postId = $postId;
    }

    /**
     * Execute the job.
     */
    public function handle(PostElasticsearchService $elasticsearchService): void
    {
        try {
            $elasticsearchService->deleteDocument(Post::getSearchIndex(), $this->postId);
        } catch (Exception $e) {
            Log::error("Failed to delete document with ID {$this->postId} from Elasticsearch index: " . $e->getMessage());
        }
    }
}
