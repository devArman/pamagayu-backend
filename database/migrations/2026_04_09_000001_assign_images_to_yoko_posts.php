<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // All 25 images on the server, sorted naturally
        $allImages = [
            'images/IMG_0385.JPG',
            'images/IMG_0388.JPG',
            'images/IMG_0395.JPG',
            'images/IMG_0399.HEIC',
            'images/IMG_0403.JPG',
            'images/IMG_0404.JPG',
            'images/IMG_0407.JPG',
            'images/IMG_0408.HEIC',
            'images/IMG_0409.HEIC',
            'images/IMG_0410.HEIC',
            'images/IMG_0411.HEIC',
            'images/IMG_0412.HEIC',
            'images/IMG_0414.HEIC',
            'images/IMG_0415.HEIC',
            'images/IMG_0416.HEIC',
            'images/IMG_0417.JPG',
            'images/IMG_0418.HEIC',
            'images/IMG_0419.JPG',
            'images/IMG_0420.HEIC',
            'images/IMG_0421.HEIC',
            'images/IMG_0424.JPG',
            'images/IMG_0425.JPG',
            'images/IMG_0426.JPG',
            'images/IMG_0427.JPG',
            'images/IMG_0429.JPG',
        ];

        // Split into 5 groups of 5
        $groups = array_chunk($allImages, 5);

        // Delete the "Photo Gallery" post created by previous migration
        $gallery = Post::where('title', 'Photo Gallery')->first();
        if ($gallery) {
            $gallery->images()->delete();
            $gallery->forceDelete();
        }

        // Get the 5 YOKO image posts (ordered by id)
        $yokoPosts = Post::where('type', 'image')
            ->where('title', 'like', 'YOKO%')
            ->orderBy('id')
            ->get();

        foreach ($yokoPosts as $index => $post) {
            if (!isset($groups[$index])) {
                break;
            }

            $imagePaths = $groups[$index];

            // Clear existing images for this post
            $post->images()->delete();

            // Set media_path to first image
            $post->update(['media_path' => $imagePaths[0]]);

            // Create gallery entries
            foreach ($imagePaths as $sortOrder => $path) {
                $post->images()->create([
                    'image_path' => $path,
                    'sort_order' => $sortOrder,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Can't reliably reverse this
    }
};
