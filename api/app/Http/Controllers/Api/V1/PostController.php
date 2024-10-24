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
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller
{
    private PostService $postService;
    private PostElasticsearchService $postElasticsearchService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        PostService              $postService,
        PostElasticsearchService $postElasticsearchService
    )
    {
        $this->postService = $postService;
        $this->postElasticsearchService = $postElasticsearchService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param PostFilterRequest $request
     * @return PostCollection
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

        return new PostCollection($posts, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PostStoreRequest $request
     * @return PostResource
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

        return new PostResource($newPost, ResourceMessagesEnum::DataCreatedSuccessfully->message(), Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return PostResource
     */
    public function show(string $id): PostResource
    {
        $post = $this->postService->getById($id);

        return new PostResource($post, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PostUpdateRequest $request
     * @param Post $post
     * @return PostResource
     */
    public function update(PostUpdateRequest $request, Post $post): PostResource
    {
        Gate::authorize('modify', $post);
        $postUpdateDto = $request->getDto();
        $this->postService->update($post->getAttribute('id'), [
            'user_id' => $postUpdateDto->user_id,
            'title' => $postUpdateDto->title,
            'content' => $postUpdateDto->content,
            'tags' => $postUpdateDto->tags,
        ]);

        return new PostResource([], ResourceMessagesEnum::DataUpdatedSuccessfully->message());
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Post $post
     * @return PostResource
     */
    public function destroy(Post $post): PostResource
    {
        Gate::authorize('modify', $post);
        $this->postService->destroy($post->getAttribute('id'));

        return new PostResource([], ResourceMessagesEnum::DataDeletedSuccessfully->message());
    }
}
