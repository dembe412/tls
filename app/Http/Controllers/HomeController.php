<?php

namespace App\Http\Controllers;

use App\Models\HeroImage;
use App\Models\HeroSetting;
use App\Models\NewsArticle;
use App\Models\Product;
use App\Support\CommunityFeed;

class HomeController extends Controller
{
    public function __invoke(CommunityFeed $feed)
    {
        $locks = Product::query()->locks()->get();
        $hero = HeroSetting::allSettings();
        $heroImages = HeroImage::query()->active()->get();

        if ($heroImages->isEmpty()) {
            $heroImages = collect([
                new HeroImage([
                    'title' => 'MineLab Gold Hardware Fleet',
                    'image_path' => 'images/hero-banner.jpg',
                    'sort_order' => 1,
                    'is_active' => true,
                ]),
            ]);
        }

        return view('home', [
            'hero' => $hero,
            'heroImages' => $heroImages,
            'products' => $locks->take(3),
            'allLocks' => $locks,
            'featuredVip' => Product::query()->vips()->first(),
            'lockCount' => $locks->count(),
            'latestNews' => NewsArticle::query()->with(['author', 'product'])->latest('published_at')->take(2)->get(),
            'community' => $feed->snapshot(),
        ]);
    }
}
