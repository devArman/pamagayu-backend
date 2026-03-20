<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PostController extends Controller
{
    public function __construct(
        private PostService $postService,
    ) {}

    public function index(Request $request): View
    {
        $query = Post::query();

        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($type = $request->input('type')) {
            $query->ofType($type);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $posts = $query->orderBy('sort_order')->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('admin.posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.posts.create');
    }

    public function store(StorePostRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['media', 'thumbnail']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $this->postService->create(
            $data,
            $request->file('media'),
            $request->file('thumbnail'),
        );

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function edit(Post $post): View
    {
        return view('admin.posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $data = $request->safe()->except(['media', 'thumbnail']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $this->postService->update(
            $post,
            $data,
            $request->file('media'),
            $request->file('thumbnail'),
        );

        return redirect()->route('admin.posts.index')->with('success', 'Post updated successfully.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->postService->delete($post);

        return redirect()->route('admin.posts.index')->with('success', 'Post deleted successfully.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $this->postService->publish($post);

        return back()->with('success', 'Post published.');
    }

    public function unpublish(Post $post): RedirectResponse
    {
        $this->postService->unpublish($post);

        return back()->with('success', 'Post unpublished.');
    }
}
