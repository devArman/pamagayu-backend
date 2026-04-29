<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Services\VideoProcessingService;
use Illuminate\Console\Command;

class ReencodeVideosCommand extends Command
{
    protected $signature = 'videos:reencode';

    protected $description = 'Re-encode video posts from HEVC to H.264, generate thumbnails, and update posts.thumbnail_path. Idempotent.';

    public function handle(VideoProcessingService $service): int
    {
        @set_time_limit(0);

        if (!$service->isFfmpegAvailable()) {
            $this->error('ffmpeg/ffprobe not found on PATH. Install with: sudo apt-get update && sudo apt-get install -y ffmpeg');
            return self::FAILURE;
        }

        $thumbsDir = storage_path('app/public/thumbs');
        if (!is_dir($thumbsDir)) {
            mkdir($thumbsDir, 0755, true);
        }

        $backupDir = storage_path('app/videos.hevc.bak');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $posts = Post::where('type', 'video')->whereNotNull('media_path')->get();

        $reencoded = 0;
        $thumbsGenerated = 0;
        $thumbsLinked = 0;
        $skipped = 0;
        $failures = 0;

        foreach ($posts as $post) {
            $absolutePath = storage_path('app/public/' . $post->media_path);

            if (!is_file($absolutePath)) {
                $this->warn("[post {$post->id}] missing file: {$post->media_path}");
                $failures++;
                continue;
            }

            try {
                $didEncode = $service->reencodeToH264IfNeeded($absolutePath, $backupDir);
                if ($didEncode) {
                    $reencoded++;
                    $this->info("[post {$post->id}] re-encoded {$post->media_path}");
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $this->error("[post {$post->id}] re-encode failed: " . $e->getMessage());
                $failures++;
                continue;
            }

            $basename = pathinfo($post->media_path, PATHINFO_FILENAME);
            $thumbStoragePath = 'thumbs/' . $basename . '.jpg';
            $thumbAbsolutePath = $thumbsDir . '/' . $basename . '.jpg';

            if (!is_file($thumbAbsolutePath)) {
                try {
                    $service->generateThumbnail($absolutePath, $thumbAbsolutePath);
                    $thumbsGenerated++;
                } catch (\Throwable $e) {
                    $this->error("[post {$post->id}] thumbnail failed: " . $e->getMessage());
                    $failures++;
                    continue;
                }
            }

            if (is_file($thumbAbsolutePath) && $post->thumbnail_path !== $thumbStoragePath) {
                $post->thumbnail_path = $thumbStoragePath;
                $post->save();
                $thumbsLinked++;
            }
        }

        $this->info(sprintf(
            'Done. re-encoded=%d, thumbs_generated=%d, thumbnail_path_updated=%d, already_h264=%d, failures=%d',
            $reencoded,
            $thumbsGenerated,
            $thumbsLinked,
            $skipped,
            $failures
        ));

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
