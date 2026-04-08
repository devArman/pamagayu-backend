<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'title',
        'description',
        'media_path',
        'thumbnail_path',
        'status',
        'sort_order',
        'is_featured',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'views_count' => 'integer',
            'sort_order' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    // Relationships

    public function images(): HasMany
    {
        return $this->hasMany(PostImage::class)->orderBy('sort_order');
    }

    // Scopes

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeFeedOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('published_at');
    }

    // Helpers

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    public function getMediaUrlAttribute(): ?string
    {
        return $this->media_path ? asset('storage/' . $this->media_path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? asset('storage/' . $this->thumbnail_path) : null;
    }

    public function getGalleryUrlsAttribute(): array
    {
        if ($this->relationLoaded('images') && $this->images->isNotEmpty()) {
            return $this->images->map(fn ($img) => asset('storage/' . $img->image_path))->all();
        }

        if (!$this->relationLoaded('images') && $this->images()->exists()) {
            return $this->images->map(fn ($img) => asset('storage/' . $img->image_path))->all();
        }

        if ($this->media_path) {
            return [asset('storage/' . $this->media_path)];
        }

        return [];
    }

    public function isGallery(): bool
    {
        return $this->isImage() && $this->images->count() > 1;
    }
}
