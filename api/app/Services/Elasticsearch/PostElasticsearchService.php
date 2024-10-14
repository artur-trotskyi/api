<?php

namespace App\Services\Elasticsearch;

use App\Repositories\Elasticsearch\PostElasticsearchRepository;
use App\Repositories\PostRepository;

class PostElasticsearchService extends BaseElasticsearchService
{
    protected PostRepository $postRepository;

    /**
     * Create a new PostElasticsearchService instance.
     *
     * @param PostElasticsearchRepository $repo The Elasticsearch repository for posts.
     * @param PostRepository $postRepository The repository for managing posts.
     */
    public function __construct
    (
        PostElasticsearchRepository $repo,
        PostRepository              $postRepository
    )
    {
        $this->repo = $repo;
        $this->postRepository = $postRepository;
    }

    /**
     * Prepare bulk data for indexing all posts.
     *
     * @param string $searchIndex The name of the Elasticsearch index.
     * @return array The prepared bulk data.
     */
    public function prepareBulkData(string $searchIndex): array
    {
        $bulkData = [];
        foreach ($this->postRepository->cursor() as $post) {
            $bulkData['body'][] = [
                'index' => [
                    '_index' => $searchIndex,
                    '_id' => $post->getKey(),
                ]
            ];
            $bulkData['body'][] = [
                'user_id' => $post->user_id,
                'title' => $post->title,
                'content' => $post->content,
                'tags' => $post->tags,
            ];
        }

        return $bulkData;
    }
}
