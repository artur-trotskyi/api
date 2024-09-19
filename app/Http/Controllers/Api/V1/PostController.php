<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\ErrorResource;
use App\Http\Resources\Post\PostCollection;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
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

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        PostService $postService
    )
    {
        $this->postService = $postService;
    }

    /**
     * Display a listing of the resource.
     * @return PostCollection
     */
    public function index(): PostCollection
    {
        $posts = $this->postService->all();

        return new PostCollection($posts, 'Posts retrieved successfully', Response::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     * @param PostStoreRequest $request
     * @return PostResource
     */
    public function store(PostStoreRequest $request): PostResource
    {
        $postRequestData = $request->validated();
        $newPost = $this->postService->create([
            'user_id' => auth()->id(),
            'title' => $postRequestData['title'],
            'content' => $postRequestData['content'],
        ]);

        return new PostResource($newPost, 'Post created successfully', Response::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     * @param string $id
     * @return ErrorResource|PostResource
     */
    public function show(string $id): ErrorResource|PostResource
    {
        $post = $this->postService->getById($id);

        return $post
            ? new PostResource($post, 'Post retrieved successfully', Response::HTTP_OK)
            : new ErrorResource(
                ['errors' => 'Failed to retrieve post'],
                'Internal server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
    }

    /**
     * Update the specified resource in storage.
     * @param PostUpdateRequest $request
     * @param Post $post
     * @return ErrorResource|PostResource
     */
    public function update(PostUpdateRequest $request, Post $post): ErrorResource|PostResource
    {
        Gate::authorize('modify', $post);
        $postRequestData = $request->validated();
        $result = $this->postService->update($post->id, [
            'user_id' => auth()->id(),
            'title' => $postRequestData['title'],
            'content' => $postRequestData['content'],
        ]);

        return $result
            ? new PostResource([], 'Post updated successfully', Response::HTTP_OK)
            : new ErrorResource(
                ['errors' => 'Failed to update post'],
                'Internal server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
    }

    /**
     * Remove the specified resource from storage.
     * @param Post $post
     * @return ErrorResource|PostResource
     */
    public function destroy(Post $post): ErrorResource|PostResource
    {
        Gate::authorize('modify', $post);
        $result = $this->postService->destroy($post->id);
        return $result
            ? new PostResource([], 'Post deleted successfully', Response::HTTP_NO_CONTENT)
            : new ErrorResource(
                ['errors' => 'Failed to delete post'],
                'Internal server error',
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
    }
}
