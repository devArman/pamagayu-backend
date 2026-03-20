<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'video' or 'image'
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('media_path');
            $table->string('thumbnail_path')->nullable();
            $table->string('status')->default('draft'); // 'draft' or 'published'
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('views_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
            $table->index('is_featured');
            $table->index('sort_order');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
