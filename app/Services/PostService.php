<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        private MediaUploadService $mediaUpload,
    ) {}

    public function create(array $data, UploadedFile|array $media, ?UploadedFile $thumbnail = null): Post
    {
        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->uploadThumbnail($thumbnail);
        }

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        if ($data['type'] === 'image') {
            $files = is_array($media) ? $media : [$media];
            $paths = $this->mediaUpload->uploadMultiple($files);

            $data['media_path'] = $paths[0];
            $post = Post::create($data);

            foreach ($paths as $index => $path) {
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }

            return $post;
        }

        $data['media_path'] = $this->mediaUpload->upload($media, $data['type']);

        return Post::create($data);
    }

    public function createFromPaths(array $data, array $mediaPaths, ?UploadedFile $thumbnail = null): Post
    {
        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->uploadThumbnail($thumbnail);
        }

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $data['media_path'] = $mediaPaths[0];
        $post = Post::create($data);

        if ($data['type'] === 'image') {
            foreach ($mediaPaths as $index => $path) {
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        }

        return $post;
    }

    public function update(Post $post, array $data, UploadedFile|array|null $media = null, ?UploadedFile $thumbnail = null): Post
    {
        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->replace($post->thumbnail_path, $thumbnail, 'image');
        }

        $type = $data['type'] ?? $post->type;

        if ($media && $type === 'image') {
            $files = is_array($media) ? $media : [$media];
            $paths = $this->mediaUpload->uploadMultiple($files);

            // Delete old gallery images from storage
            $oldPaths = $post->images->pluck('image_path')->all();
            $this->mediaUpload->deleteMultiple($oldPaths);
            $post->images()->delete();

            // Also delete old media_path if different from gallery images
            if ($post->media_path && !in_array($post->media_path, $oldPaths)) {
                $this->mediaUpload->delete($post->media_path);
            }

            $data['media_path'] = $paths[0];

            foreach ($paths as $index => $path) {
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        } elseif ($media && $type === 'video') {
            // Delete old gallery images if switching from image to video
            if ($post->images->isNotEmpty()) {
                $oldPaths = $post->images->pluck('image_path')->all();
                $this->mediaUpload->deleteMultiple($oldPaths);
                $post->images()->delete();
            }

            $data['media_path'] = $this->mediaUpload->replace($post->media_path, $media, 'video');
        }

        // Set published_at when publishing for the first time
        $newStatus = $data['status'] ?? $post->status;
        if ($newStatus === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return $post->fresh();
    }

    public function updateFromPaths(Post $post, array $data, array $mediaPaths, ?UploadedFile $thumbnail = null): Post
    {
        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->replace($post->thumbnail_path, $thumbnail, 'image');
        }

        $type = $data['type'] ?? $post->type;

        if ($type === 'image') {
            $oldPaths = $post->images->pluck('image_path')->all();
            $this->mediaUpload->deleteMultiple($oldPaths);
            $post->images()->delete();

            if ($post->media_path && !in_array($post->media_path, $oldPaths)) {
                $this->mediaUpload->delete($post->media_path);
            }

            $data['media_path'] = $mediaPaths[0];

            foreach ($mediaPaths as $index => $path) {
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        } else {
            if ($post->images->isNotEmpty()) {
                $oldPaths = $post->images->pluck('image_path')->all();
                $this->mediaUpload->deleteMultiple($oldPaths);
                $post->images()->delete();
            }
            $this->mediaUpload->delete($post->media_path);
            $data['media_path'] = $mediaPaths[0];
        }

        $newStatus = $data['status'] ?? $post->status;
        if ($newStatus === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);
        return $post->fresh();
    }

    public function delete(Post $post): void
    {
        // Delete gallery images from storage
        foreach ($post->images as $image) {
            $this->mediaUpload->delete($image->image_path);
        }

        $this->mediaUpload->delete($post->media_path);
        $this->mediaUpload->delete($post->thumbnail_path);

        $post->delete();
    }

    public function publish(Post $post): Post
    {
        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        return $post->fresh();
    }

    public function unpublish(Post $post): Post
    {
        $post->update(['status' => 'draft']);

        return $post->fresh();
    }

    public function incrementViews(Post $post): void
    {
        $post->increment('views_count');
    }
}
