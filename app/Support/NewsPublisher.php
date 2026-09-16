<?php

namespace App\Support;

use App\Models\NewsArticle;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Str;

class NewsPublisher
{
    public static function forProduct(Product $product, User $author): NewsArticle
    {
        $excerpt = $product->tagline ?: $product->name.' is now available on TSL.';

        return NewsArticle::query()->create([
            'user_id' => $author->id,
            'product_id' => $product->id,
            'slug' => 'new-lock-'.Str::slug($product->name).'-'.Str::lower(Str::random(6)),
            'title' => $product->name.' is now on TSL',
            'excerpt' => $excerpt,
            'body' => $product->name.' is in stock. Price '.$product->priceLabel().', daily interest '.$product->dailyLabel().'. Request it from the shop.',
            'badge' => 'New lock',
            'image_path' => $product->image_path,
            'published_at' => now(),
        ]);
    }
}
