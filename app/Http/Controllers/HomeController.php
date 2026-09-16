<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use App\Models\Product;
use App\Support\CommunityFeed;

class HomeController extends Controller
{
    public function __invoke(CommunityFeed $feed)
    {
        return view('home', [
            'products' => Product::query()->locks()->limit(3)->get(),
            'featuredVip' => Product::query()->vips()->first(),
            'lockCount' => Product::query()->locks()->count(),
            'latestNews' => NewsArticle::query()->with(['author', 'product'])->latest('published_at')->take(2)->get(),
            'community' => $feed->snapshot(),
        ]);
    }
}
