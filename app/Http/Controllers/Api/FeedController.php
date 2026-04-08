<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function __construct(
        private PostService $postService,
    ) {}

    public function index(Request $request): PostCollection
    {
        $query = Post::published()->feedOrdered()->with('images');

        if ($type = $request->input('type')) {
            $query->ofType($type);
        }

        return new PostCollection($query->paginate($request->integer('per_page', 15)));
    }

    public function show(Post $post): PostResource|JsonResponse
    {
        if (!$post->isPublished()) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $post->load('images');

        return new PostResource($post);
    }

    public function view(Post $post): JsonResponse
    {
        if (!$post->isPublished()) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $this->postService->incrementViews($post);

        return response()->json(['message' => 'View recorded.']);
    }

    public function featured(): PostCollection
    {
        $posts = Post::published()->featured()->feedOrdered()->with('images')->paginate(15);

        return new PostCollection($posts);
    }
}
