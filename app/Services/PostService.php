<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\UploadedFile;

class PostService
{
    public function __construct(
        private MediaUploadService $mediaUpload,
    ) {}

    public function create(array $data, UploadedFile $media, ?UploadedFile $thumbnail = null): Post
    {
        $data['media_path'] = $this->mediaUpload->upload($media, $data['type']);

        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->uploadThumbnail($thumbnail);
        }

        if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return Post::create($data);
    }

    public function update(Post $post, array $data, ?UploadedFile $media = null, ?UploadedFile $thumbnail = null): Post
    {
        if ($media) {
            $data['media_path'] = $this->mediaUpload->replace($post->media_path, $media, $data['type'] ?? $post->type);
        }

        if ($thumbnail) {
            $data['thumbnail_path'] = $this->mediaUpload->replace($post->thumbnail_path, $thumbnail, 'image');
        }

        // Set published_at when publishing for the first time
        $newStatus = $data['status'] ?? $post->status;
        if ($newStatus === 'published' && !$post->published_at) {
            $data['published_at'] = now();
        }

        $post->update($data);

        return $post->fresh();
    }

    public function delete(Post $post): void
    {
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
