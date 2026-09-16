@extends('layouts.app')

@section('title', 'TSL Smart Locks — available now')
@section('description', 'Browse TSL Tuya smart locks for purchase. Each lock pays a daily amount and you cash out after 35 days.')

@section('content')
@php $tab = 'home'; @endphp

<header class="hero">
    <div class="hero-split">
        <div class="hero-copy">
            <p class="kicker">Available now</p>
            <h1>Pick a lock.<br><span>Own it. Earn daily.</span></h1>
            <p class="sub">Each lock pays a daily amount set by TSL. Tap any lock to start.</p>
        </div>
        @include('partials.community-card')
    </div>
    <div class="stat-row">
        <div class="stat"><b>{{ $lockCount }}</b><span>locks in stock</span></div>
        <div class="stat"><b>Daily</b><span>interest per lock</span></div>
        <div class="stat"><b>35</b><span>days to cash out</span></div>
    </div>
    <div class="home-actions">
        <a class="home-action" href="{{ route('faq') }}">
            <strong>FAQ</strong>
            <span>Frequently asked questions</span>
        </a>
        <a class="home-action home-action-bonus" href="{{ route('bonus') }}">
            <img src="{{ asset('images/gift.png') }}" alt="" width="160" height="148">
            <span class="home-action-copy">
                <strong>Redeem bonus</strong>
                <span>Enter a TSL bonus code</span>
            </span>
        </a>
    </div>
</header>

<section class="catalog">
    <div class="section-head">
        <h2>Locks for purchase</h2>
        <a href="{{ route('products') }}">See all</a>
    </div>
    <div class="lock-grid">
        @foreach ($products as $product)
            @include('partials.lock-card', ['product' => $product])
        @endforeach
        @if ($featuredVip)
            @include('partials.lock-card', ['product' => $featuredVip])
        @endif
    </div>
</section>

@if ($latestNews->isNotEmpty())
<section class="catalog">
    <div class="section-head">
        <h2>TSL news</h2>
        <a href="{{ route('news') }}">See all</a>
    </div>
    <div class="news-feed">
        @foreach ($latestNews as $article)
            @include('partials.news-post', ['article' => $article])
        @endforeach
    </div>
</section>
@endif
@endsection
