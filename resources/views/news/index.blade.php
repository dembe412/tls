@extends('layouts.app')

@section('title', 'TSL news')
@section('description', 'Updates, new locks and posts from the TSL manager.')

@section('content')
<header class="page-head">
    <p class="kicker">From the desk</p>
    <h1>News</h1>
    <p class="sub">New locks and posts from the TSL manager.</p>
</header>

<section class="news-feed">
    @forelse ($articles as $article)
        @include('partials.news-post', ['article' => $article])
    @empty
        <p class="muted">No posts yet.</p>
    @endforelse
</section>
@endsection
