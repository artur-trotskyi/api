<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ResourceMessagesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostFilterRequest;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\Post\PostCollection;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use App\Services\Elasticsearch\PostElasticsearchService;
use App\Services\PostService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Log;

class PostController extends Controller implements HasMiddleware
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        private readonly PostService $postService,
        private readonly PostElasticsearchService $postElasticsearchService
    ) {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('can:modify,post', only: ['update', 'destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(PostFilterRequest $request): PostCollection
    {
        $postFilterDto = $request->getDto();
        $q = $postFilterDto->q;
        $itemsPerPage = $postFilterDto->itemsPerPage;
        $page = $postFilterDto->page;
        $title = $postFilterDto->title;
        $content = $postFilterDto->content;
        $sortBy = $postFilterDto->sortBy;
        $orderBy = $postFilterDto->orderBy;

        $posts = $this->postElasticsearchService->search($q, $itemsPerPage, $page, ['title' => $title, 'content' => $content], $sortBy, $orderBy);
        //   $posts = $this->postService->filter($q, $itemsPerPage, $page, ['title' => $title, 'content' => $content], $sortBy, $orderBy);

        Log::info('Post filter applied.', ['$postFilterDto' => $postFilterDto]);

        return (new PostCollection($posts))
            ->withStatusMessage(true, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostStoreRequest $request): PostResource
    {
        $postStoreDto = $request->getDto();
        $newPost = $this->postService->create([
            'user_id' => $postStoreDto->user_id,
            'title' => $postStoreDto->title,
            'content' => $postStoreDto->content,
            'tags' => $postStoreDto->tags,
        ]);

        return (new PostResource($newPost))
            ->withStatusMessage(true, ResourceMessagesEnum::DataCreatedSuccessfully->message());
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): PostResource
    {
        $post = $this->postService->getById($id);

        return (new PostResource($post))
            ->withStatusMessage(true, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostUpdateRequest $request, Post $post): PostResource
    {
        $postUpdateDto = $request->getDto();
        $this->postService->update($post->getAttribute('id'), [
            'user_id' => $postUpdateDto->user_id,
            'title' => $postUpdateDto->title,
            'content' => $postUpdateDto->content,
            'tags' => $postUpdateDto->tags,
        ]);

        return (new PostResource([]))
            ->withStatusMessage(true, ResourceMessagesEnum::DataUpdatedSuccessfully->message());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post): PostResource
    {
        $this->postService->destroy($post->getAttribute('id'));

        return (new PostResource([]))
            ->withStatusMessage(true, ResourceMessagesEnum::DataDeletedSuccessfully->message());
    }
}
