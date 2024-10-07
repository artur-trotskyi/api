<?php

namespace App\Http\Controllers\Api\V1;

use App\Constants\AppConstants;
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
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PostController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', except: ['index', 'show']),
        ];
    }

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

        return new PostCollection($posts, AppConstants::RESOURCE_MESSAGES['data_retrieved_successfully']);
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

        return new PostResource($newPost, AppConstants::RESOURCE_MESSAGES['data_created_successfully'], Response::HTTP_CREATED);
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

        return new PostResource($post, AppConstants::RESOURCE_MESSAGES['data_retrieved_successfully']);
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
        $postUpdateDto = $request->validated();
        $this->postService->update($post->getAttribute('id'), [
            'user_id' => $postUpdateDto->user_id,
            'title' => $postUpdateDto->title,
            'content' => $postUpdateDto->content,
            'tags' => $postUpdateDto->tags,
        ]);

        return new PostResource([], AppConstants::RESOURCE_MESSAGES['data_updated_successfully']);
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

        return new PostResource([], AppConstants::RESOURCE_MESSAGES['data_deleted_successfully']);
    }
}
