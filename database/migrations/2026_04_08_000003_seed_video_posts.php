<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure storage symlink exists
        $link = public_path('storage');
        if (!file_exists($link)) {
            \Artisan::call('storage:link');
        }

        // Move videos from public/videos/ to storage/app/public/videos/
        $publicVideosDir = public_path('videos');
        if (is_dir($publicVideosDir)) {
            $storageVideosDir = storage_path('app/public/videos');
            if (!is_dir($storageVideosDir)) {
                mkdir($storageVideosDir, 0755, true);
            }

            $videos = glob($publicVideosDir . '/*.mp4');
            // Sort naturally: video1, video2, ..., video10, video11
            natsort($videos);

            $order = 0;
            foreach ($videos as $videoPath) {
                $filename = basename($videoPath);
                $storagePath = 'videos/' . $filename;
                $destPath = $storageVideosDir . '/' . $filename;

                // Copy file to storage (don't delete original yet)
                if (!file_exists($destPath)) {
                    copy($videoPath, $destPath);
                }

                // Extract a clean title from filename (video1.mp4 -> Video 1)
                $name = pathinfo($filename, PATHINFO_FILENAME);
                $number = preg_replace('/[^0-9]/', '', $name);
                $title = 'Video ' . $number;

                // Create post if not already exists
                Post::firstOrCreate(
                    ['media_path' => $storagePath],
                    [
                        'type' => 'video',
                        'title' => $title,
                        'description' => $title,
                        'media_path' => $storagePath,
                        'status' => 'published',
                        'sort_order' => $order,
                        'is_featured' => false,
                        'published_at' => now(),
                    ]
                );

                $order++;
            }
        }

        // Move images from public/images/ to storage/app/public/images/
        $publicImagesDir = public_path('images');
        if (is_dir($publicImagesDir)) {
            $storageImagesDir = storage_path('app/public/images');
            if (!is_dir($storageImagesDir)) {
                mkdir($storageImagesDir, 0755, true);
            }

            $images = glob($publicImagesDir . '/*.{JPG,jpg,jpeg,png,webp,HEIC,heic}', GLOB_BRACE);
            natsort($images);

            foreach ($images as $imagePath) {
                $filename = basename($imagePath);
                $storagePath = 'images/' . $filename;
                $destPath = $storageImagesDir . '/' . $filename;

                if (!file_exists($destPath)) {
                    copy($imagePath, $destPath);
                }
            }

            // Create a single image gallery post with all images
            $allImages = glob($storageImagesDir . '/*.{JPG,jpg,jpeg,png,webp,HEIC,heic}', GLOB_BRACE);
            natsort($allImages);
            $imagePaths = array_map(fn ($p) => 'images/' . basename($p), $allImages);

            if (!empty($imagePaths)) {
                $firstPath = reset($imagePaths);
                $post = Post::firstOrCreate(
                    ['title' => 'Photo Gallery'],
                    [
                        'type' => 'image',
                        'title' => 'Photo Gallery',
                        'description' => 'Photo collection',
                        'media_path' => $firstPath,
                        'status' => 'published',
                        'sort_order' => 100,
                        'is_featured' => false,
                        'published_at' => now(),
                    ]
                );

                // Add gallery images if the post was just created
                if ($post->wasRecentlyCreated) {
                    foreach (array_values($imagePaths) as $index => $path) {
                        $post->images()->create([
                            'image_path' => $path,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }
        }
    }

    public function down(): void
    {
        Post::where('type', 'video')
            ->where('title', 'like', 'Video %')
            ->delete();

        Post::where('title', 'Photo Gallery')->delete();
    }
};
