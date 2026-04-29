<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    public function up(): void
    {
        @set_time_limit(0);

        // Delegates to the idempotent `videos:reencode` command so the work
        // can be re-run by hand later (e.g. if ffmpeg wasn't installed yet
        // when this migration first ran).
        try {
            Artisan::call('videos:reencode');
            logger()->info('videos:reencode output: ' . Artisan::output());
        } catch (\Throwable $e) {
            // Don't crash the deploy. Re-run `php artisan videos:reencode`
            // manually after fixing the underlying issue.
            logger()->error('videos:reencode failed during migration: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        // Re-encoding is not reversible. Originals are preserved in
        // storage/app/videos.hevc.bak/ on the server for manual rollback.
    }
};
