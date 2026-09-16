<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;

class NewsController extends Controller
{
    public function index()
    {
        return view('news.index', [
            'articles' => NewsArticle::query()->with(['author', 'product'])->latest('published_at')->get(),
        ]);
    }

    public function show(NewsArticle $article)
    {
        $article->load(['author', 'product']);

        return view('news.show', [
            'article' => $article,
        ]);
    }
}
