<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminNewsController extends Controller
{
    public function create()
    {
        return view('admin.news.form', [
            'article' => new NewsArticle(['badge' => 'Post']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['image_path'] = $this->storeImage($request);
        $data['published_at'] = now();

        NewsArticle::query()->create($data);

        return redirect()->route('admin.index')->with('success', 'Post published to News.');
    }

    public function edit(NewsArticle $article)
    {
        return view('admin.news.form', [
            'article' => $article,
        ]);
    }

    public function update(Request $request, NewsArticle $article)
    {
        $data = $this->validated($request);

        if ($path = $this->storeImage($request)) {
            if ($article->image_path && $article->image_path !== $article->product?->image_path) {
                Storage::disk('public')->delete($article->image_path);
            }
            $data['image_path'] = $path;
        }

        $article->update($data);

        if (! $article->user_id) {
            $article->update(['user_id' => $request->user()->id]);
        }

        return redirect()->route('admin.index')->with('success', 'Post updated.');
    }

    public function destroy(NewsArticle $article)
    {
        if ($article->image_path && $article->image_path !== $article->product?->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        $article->delete();

        return redirect()->route('admin.index')->with('success', 'Post removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:4000'],
            'badge' => ['nullable', 'string', 'max:30'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $data['badge'] = $data['badge'] ?: 'Post';
        $data['excerpt'] = Str::limit(trim(preg_replace('/\s+/', ' ', $data['body'])), 140);
        unset($data['image']);

        return $data;
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 1;

        while (NewsArticle::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    private function storeImage(Request $request): ?string
    {
        if (! $request->hasFile('image')) {
            return null;
        }

        return $request->file('image')->store('news', 'public');
    }
}
