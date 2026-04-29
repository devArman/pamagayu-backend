<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class VideoProcessingService
{
    private const TARGET_BITRATE = '3000k';
    private const MAX_BITRATE = '4500k';
    private const BUF_SIZE = '6000k';
    private const REENCODE_TIMEOUT_SECONDS = 1200;
    private const PROBE_TIMEOUT_SECONDS = 30;
    private const THUMB_TIMEOUT_SECONDS = 60;

    public function isFfmpegAvailable(): bool
    {
        $ffmpeg = Process::run(['ffmpeg', '-version']);
        $ffprobe = Process::run(['ffprobe', '-version']);

        return $ffmpeg->successful() && $ffprobe->successful();
    }

    public function getVideoCodec(string $absolutePath): ?string
    {
        $result = Process::timeout(self::PROBE_TIMEOUT_SECONDS)->run([
            'ffprobe',
            '-v', 'error',
            '-select_streams', 'v:0',
            '-show_entries', 'stream=codec_name',
            '-of', 'csv=p=0',
            $absolutePath,
        ]);

        if (!$result->successful()) {
            return null;
        }

        $codec = trim($result->output());

        return $codec === '' ? null : $codec;
    }

    /**
     * Re-encode the video at $absolutePath to H.264 in-place if it isn't already H.264.
     * Original file is moved to $backupDir before being replaced.
     *
     * @throws \RuntimeException
     */
    public function reencodeToH264IfNeeded(string $absolutePath, ?string $backupDir = null): bool
    {
        $codec = $this->getVideoCodec($absolutePath);

        if ($codec === 'h264') {
            return false;
        }

        $tempOutput = $this->tempEncodePath($absolutePath);

        $result = Process::timeout(self::REENCODE_TIMEOUT_SECONDS)->run([
            'ffmpeg',
            '-y', '-hide_banner', '-loglevel', 'error',
            '-i', $absolutePath,
            '-c:v', 'libx264',
            '-preset', 'slow',
            '-profile:v', 'high',
            '-level', '4.0',
            '-pix_fmt', 'yuv420p',
            '-b:v', self::TARGET_BITRATE,
            '-maxrate', self::MAX_BITRATE,
            '-bufsize', self::BUF_SIZE,
            '-movflags', '+faststart',
            '-c:a', 'aac',
            '-b:a', '128k',
            '-ar', '44100',
            $tempOutput,
        ]);

        if (!$result->successful()) {
            @unlink($tempOutput);
            throw new \RuntimeException('ffmpeg re-encode failed: ' . $result->errorOutput());
        }

        if ($backupDir && is_dir($backupDir)) {
            $backupPath = rtrim($backupDir, '/\\') . DIRECTORY_SEPARATOR . basename($absolutePath);
            if (!file_exists($backupPath)) {
                @copy($absolutePath, $backupPath);
            }
        }

        if (!@rename($tempOutput, $absolutePath)) {
            @unlink($tempOutput);
            throw new \RuntimeException("Could not replace original at {$absolutePath}");
        }

        return true;
    }

    /**
     * Grab a JPEG thumbnail from the 1-second mark of the source video.
     *
     * @throws \RuntimeException
     */
    public function generateThumbnail(string $sourceAbsolutePath, string $thumbAbsolutePath): void
    {
        $dir = dirname($thumbAbsolutePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $result = Process::timeout(self::THUMB_TIMEOUT_SECONDS)->run([
            'ffmpeg',
            '-y', '-hide_banner', '-loglevel', 'error',
            '-ss', '1',
            '-i', $sourceAbsolutePath,
            '-vframes', '1',
            '-q:v', '3',
            '-vf', 'scale=720:-2',
            $thumbAbsolutePath,
        ]);

        if (!$result->successful() || !is_file($thumbAbsolutePath)) {
            throw new \RuntimeException('ffmpeg thumbnail failed: ' . $result->errorOutput());
        }
    }

    private function tempEncodePath(string $absolutePath): string
    {
        $dir = dirname($absolutePath);
        $base = pathinfo($absolutePath, PATHINFO_FILENAME);

        return $dir . DIRECTORY_SEPARATOR . $base . '.h264.tmp.mp4';
    }

    /**
     * Process a freshly uploaded video that lives under the public disk:
     *   - Re-encode to H.264 in place if needed
     *   - Produce a JPEG thumbnail at thumbs/{basename}.jpg
     *
     * Returns the relative storage path of the generated thumbnail, or null
     * on any failure (failures are logged; uploads should not 500 because
     * ffmpeg is missing or rejected the file).
     */
    public function processUploadedVideo(string $relativeMediaPath): ?string
    {
        if (!$this->isFfmpegAvailable()) {
            logger()->warning('ffmpeg unavailable; skipping video post-processing for ' . $relativeMediaPath);
            return null;
        }

        $absolutePath = storage_path('app/public/' . ltrim($relativeMediaPath, '/'));
        if (!is_file($absolutePath)) {
            logger()->warning('Video file not found for post-processing: ' . $absolutePath);
            return null;
        }

        try {
            $this->reencodeToH264IfNeeded($absolutePath);
        } catch (\Throwable $e) {
            logger()->error('Re-encode failed during upload: ' . $e->getMessage());
            return null;
        }

        $basename = pathinfo($relativeMediaPath, PATHINFO_FILENAME);
        $thumbRelative = 'thumbs/' . $basename . '.jpg';
        $thumbAbsolute = storage_path('app/public/' . $thumbRelative);

        try {
            $this->generateThumbnail($absolutePath, $thumbAbsolute);
        } catch (\Throwable $e) {
            logger()->error('Thumbnail generation failed during upload: ' . $e->getMessage());
            return null;
        }

        return is_file($thumbAbsolute) ? $thumbRelative : null;
    }
}
