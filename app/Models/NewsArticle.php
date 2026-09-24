<?php

namespace App\Models;

use App\Support\Media;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewsArticle extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'slug',
        'title',
        'excerpt',
        'body',
        'badge',
        'image_path',
        'published_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path) {
            return Media::url($this->image_path);
        }

        return $this->product?->imageUrl();
    }

    public function authorName(): string
    {
        return $this->author?->profileName() ?? 'TSL';
    }

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }
}
