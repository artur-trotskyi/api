<?php

namespace App\Observers;

use App\Jobs\Elasticsearch\PostRemoveElasticsearchJob;
use App\Models\Post;
use Illuminate\Foundation\Bus\DispatchesJobs;

class PostObserver
{
    use DispatchesJobs;

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void {}

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void {}

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        $job = new PostRemoveElasticsearchJob($post->id);
        $this->dispatch($job);
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void {}

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        $job = new PostRemoveElasticsearchJob($post->id);
        $this->dispatch($job);
    }
}
