<?php

use App\Models\Post;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Replaces production HEVC videos with H.264 versions that were
     * re-encoded locally and committed to public/videos/. Also drops the
     * generated thumbnails into storage and links them on each video Post.
     *
     * Done this way because ffmpeg isn't installed on the Forge box; doing
     * the encoding offline avoids needing it server-side.
     */
    public function up(): void
    {
        @set_time_limit(0);

        $publicVideos = public_path('videos');
        $publicThumbs = public_path('thumbs');
        $storageVideos = storage_path('app/public/videos');
        $storageThumbs = storage_path('app/public/thumbs');
        $backupDir = storage_path('app/videos.hevc.bak');

        foreach ([$storageVideos, $storageThumbs, $backupDir] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }

        // 1. Replace each storage video file with the re-encoded copy from public/videos/.
        //    Keep the original HEVC bytes in storage/app/videos.hevc.bak/ for rollback.
        foreach (glob($publicVideos . '/video*.mp4') ?: [] as $sourcePath) {
            $filename = basename($sourcePath);
            $destPath = $storageVideos . '/' . $filename;

            if (file_exists($destPath) && md5_file($destPath) === md5_file($sourcePath)) {
                continue;
            }

            if (file_exists($destPath)) {
                $backupPath = $backupDir . '/' . $filename;
                if (!file_exists($backupPath)) {
                    @copy($destPath, $backupPath);
                }
            }

            $tmpPath = $destPath . '.tmp';
            if (!@copy($sourcePath, $tmpPath)) {
                logger()->error("Failed to stage {$tmpPath}");
                continue;
            }
            if (!@rename($tmpPath, $destPath)) {
                @unlink($tmpPath);
                logger()->error("Failed to swap in {$destPath}");
                continue;
            }
        }

        // 2. Copy each thumbnail into storage so /storage/thumbs/videoN.jpg resolves.
        foreach (glob($publicThumbs . '/video*.jpg') ?: [] as $sourcePath) {
            $filename = basename($sourcePath);
            $destPath = $storageThumbs . '/' . $filename;

            if (file_exists($destPath) && md5_file($destPath) === md5_file($sourcePath)) {
                continue;
            }
            @copy($sourcePath, $destPath);
        }

        // 3. Set posts.thumbnail_path for every video post that has a matching thumbnail on disk.
        Post::where('type', 'video')->whereNotNull('media_path')->get()->each(function (Post $post) use ($storageThumbs) {
            $basename = pathinfo($post->media_path, PATHINFO_FILENAME);
            $thumbAbsolute = $storageThumbs . '/' . $basename . '.jpg';
            $thumbRelative = 'thumbs/' . $basename . '.jpg';

            if (is_file($thumbAbsolute) && $post->thumbnail_path !== $thumbRelative) {
                $post->thumbnail_path = $thumbRelative;
                $post->save();
            }
        });
    }

    public function down(): void
    {
        // Originals are preserved in storage/app/videos.hevc.bak/ for manual rollback.
    }
};
