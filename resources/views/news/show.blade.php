@extends('layouts.app')

@section('title', $article->title.' — TSL')
@section('description', $article->excerpt)

@section('content')
<article class="article post-article">
    <a class="back" href="{{ route('news') }}">← All news</a>
    <header class="post-head">
        @include('partials.avatar', ['person' => $article->author, 'size' => 'lg'])
        <div>
            <strong>{{ $article->authorName() }}</strong>
            <span>{{ $article->author?->isAdmin() ? 'Manager' : 'TSL' }} · {{ $article->published_at->format('F j, Y') }}</span>
        </div>
        <em class="badge">{{ $article->badge }}</em>
    </header>
    <h1>{{ $article->title }}</h1>
    @if ($article->imageUrl())
        <img class="post-hero" src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
    @endif
    <div class="prose">
        {!! nl2br(e($article->body)) !!}
    </div>
</article>
@endsection
