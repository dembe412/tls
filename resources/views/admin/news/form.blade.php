@extends('layouts.app')

@section('title', ($article->exists ? 'Edit post' : 'New post').' — TSL')

@section('content')
<header class="page-head compact">
    <a class="back" href="{{ route('admin.index') }}">← Manager console</a>
    <p class="kicker">News</p>
    <h1>{{ $article->exists ? 'Edit post' : 'Write a post' }}</h1>
    <p class="sub">This appears on News with your profile name and photo.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card product-form">
        <form
            method="POST"
            action="{{ $article->exists ? route('admin.news.update', $article) : route('admin.news.store') }}"
            enctype="multipart/form-data"
            class="auth-form"
        >
            @csrf
            @if ($article->exists)
                @method('PUT')
            @endif
            <label>
                <span>Title</span>
                <input class="field-pill" name="title" value="{{ old('title', $article->title) }}" placeholder="New lock week" required>
            </label>
            <label>
                <span>Badge</span>
                <input class="field-pill" name="badge" value="{{ old('badge', $article->badge) }}" placeholder="Post">
            </label>
            <label>
                <span>Write the post</span>
                <textarea class="field-area" name="body" rows="6" required placeholder="Tell clients what is new...">{{ old('body', $article->body) }}</textarea>
            </label>
            <label>
                <span>Picture</span>
                <input class="file-input" type="file" name="image" accept="image/jpeg,image/png,image/webp" data-max-upload-mb="2">
                <small class="hint">JPG, PNG or WebP. Shown like a post photo. Maximum 2 MB.</small>
            </label>
            @if ($article->exists && $article->imageUrl())
                <div class="preview-lock">
                    <img src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
                    <span>Current post photo</span>
                </div>
            @endif
            <button class="btn btn-primary" type="submit">
                {{ $article->exists ? 'Save post' : 'Publish post' }}
            </button>
        </form>
    </div>
</section>
@endsection
