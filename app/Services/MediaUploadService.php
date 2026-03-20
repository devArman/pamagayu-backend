<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class MediaUploadService
{
    public function upload(UploadedFile $file, string $type): string
    {
        $directory = $type === 'video' ? 'videos' : 'images';

        return $file->store($directory, 'public');
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
}
