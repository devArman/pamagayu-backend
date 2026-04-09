<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePostRequest;
use App\Http\Requests\Admin\UpdatePostRequest;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\RedirectResponse;
use App\Services\MediaUploadService;
use Illuminate\Http\JsonResponse;
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
        set_time_limit(300);

        $data = $request->safe()->except(['media', 'thumbnail', 'uploaded_media']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        // Support pre-uploaded files (via AJAX upload endpoint)
        $uploadedPaths = $request->input('uploaded_media', []);
        if (!empty($uploadedPaths)) {
            $this->postService->createFromPaths($data, $uploadedPaths, $request->file('thumbnail'));
        } else {
            $this->postService->create($data, $request->file('media'), $request->file('thumbnail'));
        }

        return redirect()->route('admin.posts.index')->with('success', 'Post created successfully.');
    }

    public function uploadChunk(Request $request): JsonResponse
    {
        set_time_limit(300);

        $request->validate([
            'chunk' => ['required', 'file'],
            'chunk_index' => ['required', 'integer', 'min:0'],
            'total_chunks' => ['required', 'integer', 'min:1'],
            'upload_id' => ['required', 'string'],
            'filename' => ['required', 'string'],
        ]);

        $uploadId = preg_replace('/[^a-zA-Z0-9_-]/', '', $request->input('upload_id'));
        $chunkIndex = $request->integer('chunk_index');
        $totalChunks = $request->integer('total_chunks');
        $originalFilename = $request->input('filename');

        $tmpDir = storage_path('app/chunks/' . $uploadId);
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $request->file('chunk')->move($tmpDir, 'chunk_' . $chunkIndex);

        // Check if all chunks received
        $receivedChunks = count(glob($tmpDir . '/chunk_*'));
        if ($receivedChunks < $totalChunks) {
            return response()->json(['status' => 'partial', 'received' => $receivedChunks]);
        }

        // All chunks received — assemble the file
        $ext = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION));
        $isVideo = in_array($ext, ['mp4', 'mov', 'webm']);
        $directory = $isVideo ? 'videos' : 'images';

        $storageDir = storage_path('app/public/' . $directory);
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $finalFilename = \Illuminate\Support\Str::random(40) . '.' . $ext;
        $finalPath = $storageDir . '/' . $finalFilename;

        $out = fopen($finalPath, 'wb');
        for ($i = 0; $i < $totalChunks; $i++) {
            $chunkPath = $tmpDir . '/chunk_' . $i;
            $in = fopen($chunkPath, 'rb');
            stream_copy_to_stream($in, $out);
            fclose($in);
            unlink($chunkPath);
        }
        fclose($out);
        rmdir($tmpDir);

        $storagePath = $directory . '/' . $finalFilename;

        // If HEIC, convert to JPEG
        if (in_array($ext, ['heic', 'heif'])) {
            try {
                $jpgFilename = \Illuminate\Support\Str::random(40) . '.jpg';
                $jpgPath = $storageDir . '/' . $jpgFilename;

                $imagick = new \Imagick();
                $imagick->readImage($finalPath);
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompressionQuality(85);

                $orientation = $imagick->getImageOrientation();
                switch ($orientation) {
                    case \Imagick::ORIENTATION_BOTTOMRIGHT:
                        $imagick->rotateImage('#000', 180);
                        break;
                    case \Imagick::ORIENTATION_RIGHTTOP:
                        $imagick->rotateImage('#000', 90);
                        break;
                    case \Imagick::ORIENTATION_LEFTBOTTOM:
                        $imagick->rotateImage('#000', -90);
                        break;
                }
                $imagick->setImageOrientation(\Imagick::ORIENTATION_TOPLEFT);
                $imagick->writeImage($jpgPath);
                $imagick->destroy();

                unlink($finalPath);
                $storagePath = $directory . '/' . $jpgFilename;
            } catch (\Exception $e) {
                // If Imagick fails, keep original file
            }
        }

        return response()->json(['status' => 'complete', 'path' => $storagePath]);
    }

    public function edit(Post $post): View
    {
        $post->load('images');

        return view('admin.posts.edit', compact('post'));
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        set_time_limit(300);

        $data = $request->safe()->except(['media', 'thumbnail', 'uploaded_media']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $uploadedPaths = $request->input('uploaded_media', []);
        if (!empty($uploadedPaths)) {
            $this->postService->updateFromPaths($post, $data, $uploadedPaths, $request->file('thumbnail'));
        } else {
            $this->postService->update($post, $data, $request->file('media'), $request->file('thumbnail'));
        }

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
