<?php

use App\Models\Post;
use App\Models\PostImage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $disk = Storage::disk('public');
        $storageDir = storage_path('app/public/images');

        // Find all HEIC files in storage
        $heicFiles = glob($storageDir . '/*.{HEIC,heic,HEIF,heif}', GLOB_BRACE);

        $converted = [];

        foreach ($heicFiles as $heicPath) {
            $oldFilename = basename($heicPath);
            $oldStoragePath = 'images/' . $oldFilename;
            $newFilename = pathinfo($oldFilename, PATHINFO_FILENAME) . '.jpg';
            $newStoragePath = 'images/' . $newFilename;
            $newFullPath = $storageDir . '/' . $newFilename;

            // Skip if already converted
            if (file_exists($newFullPath)) {
                $converted[$oldStoragePath] = $newStoragePath;
                continue;
            }

            try {
                $imagick = new \Imagick();
                $imagick->readImage($heicPath);
                $imagick->setImageFormat('jpeg');
                $imagick->setImageCompressionQuality(85);

                // Auto-orient based on EXIF
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

                $imagick->writeImage($newFullPath);
                $imagick->destroy();

                $converted[$oldStoragePath] = $newStoragePath;
            } catch (\Exception $e) {
                // Log but continue
                logger()->warning("HEIC conversion failed for {$oldFilename}: " . $e->getMessage());
            }
        }

        // Update database references
        foreach ($converted as $oldPath => $newPath) {
            // Update post_images table
            PostImage::where('image_path', $oldPath)->update(['image_path' => $newPath]);

            // Update posts table media_path
            Post::where('media_path', $oldPath)->update(['media_path' => $newPath]);

            // Update posts table thumbnail_path
            Post::where('thumbnail_path', $oldPath)->update(['thumbnail_path' => $newPath]);
        }
    }

    public function down(): void
    {
        // Cannot reverse conversion
    }
};
