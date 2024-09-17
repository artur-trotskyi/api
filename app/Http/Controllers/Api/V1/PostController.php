<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PostStoreRequest;
use App\Http\Requests\Post\PostUpdateRequest;
use App\Http\Resources\Post\PostCollection;
use App\Http\Resources\Post\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PostController extends Controller
{
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

        return new PostCollection($posts);
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
            'title' => $postRequestData['title'],
            'content' => $postRequestData['content'],
        ]);

        return new PostResource($newPost);
    }

    /**
     * Display the specified resource.
     * @param string $id
     * @return PostResource
     */
    public function show(string $id): PostResource
    {
        $post = $this->postService->getById($id);

        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     * @param PostUpdateRequest $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(PostUpdateRequest $request, string $id): JsonResponse
    {
        $postRequestData = $request->validated();
        $result = $this->postService->update($id, [
            'title' => $postRequestData['title'],
            'content' => $postRequestData['content'],
        ]);

        return $result
            ? response()->json(['message' => 'Post Update Successful'], 201)
            : response()->json(['error' => 'Failed to update post'], 500);
    }

    /**
     * Remove the specified resource from storage.
     * @param string $id
     * @return Response|JsonResponse
     */
    public function destroy(string $id): Response|JsonResponse
    {
        $result = $this->postService->destroy($id);

        return $result
            ? response()->noContent()
            : response()->json(['error' => 'Failed to destroy post'], 500);
    }
}
