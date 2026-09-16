<article class="post-card">
    <header class="post-head">
        @include('partials.avatar', ['person' => $article->author, 'size' => 'md'])
        <div>
            <strong>{{ $article->authorName() }}</strong>
            <span>{{ $article->author?->isAdmin() ? 'Manager' : 'TSL' }} · {{ $article->published_at->diffForHumans() }}</span>
        </div>
        <em class="badge">{{ $article->badge }}</em>
    </header>
    <a class="post-body" href="{{ route('news.show', $article) }}">
        <h2>{{ $article->title }}</h2>
        <p>{{ $article->excerpt }}</p>
        @if ($article->imageUrl())
            <img src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
        @endif
    </a>
</article>
