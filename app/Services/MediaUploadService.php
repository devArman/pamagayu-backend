<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaUploadService
{
    private const HEIC_EXTENSIONS = ['heic', 'heif'];

    public function upload(UploadedFile $file, string $type): string
    {
        $directory = $type === 'video' ? 'videos' : 'images';

        if ($this->isHeic($file)) {
            return $this->convertHeicAndStore($file, $directory);
        }

        return $file->store($directory, 'public');
    }

    private function isHeic(UploadedFile $file): bool
    {
        $ext = strtolower($file->getClientOriginalExtension());
        return in_array($ext, self::HEIC_EXTENSIONS);
    }

    private function convertHeicAndStore(UploadedFile $file, string $directory): string
    {
        $filename = Str::random(40) . '.jpg';
        $storagePath = $directory . '/' . $filename;
        $fullPath = Storage::disk('public')->path($storagePath);

        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        // Use Imagick to convert HEIC to JPEG
        $imagick = new \Imagick();
        $imagick->readImage($file->getPathname());
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompressionQuality(85);

        // Auto-orient based on EXIF data
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

        $imagick->writeImage($fullPath);
        $imagick->destroy();

        return $storagePath;
    }

    public function uploadThumbnail(UploadedFile $file): string
    {
        return $file->store('thumbnails', 'public');
    }

    public function delete(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    public function replace(?string $oldPath, UploadedFile $newFile, string $type): string
    {
        $this->delete($oldPath);

        return $this->upload($newFile, $type);
    }

    /**
     * @param  UploadedFile[]  $files
     * @return string[]
     */
    public function uploadMultiple(array $files, string $type = 'image'): array
    {
        $paths = [];
        foreach ($files as $file) {
            $paths[] = $this->upload($file, $type);
        }
        return $paths;
    }

    /**
     * @param  string[]  $paths
     */
    public function deleteMultiple(array $paths): void
    {
        foreach ($paths as $path) {
            $this->delete($path);
        }
    }
}
