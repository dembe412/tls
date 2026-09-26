@extends('layouts.app')

@section('title', 'TSL Smart Locks — available now')
@section('description', 'Browse TSL Tuya smart locks for purchase. Earn daily interest and cash out any day once you reach 2,000 UGX.')

@section('content')
@php $tab = 'home'; @endphp

<header class="hero minelab-hero">
    {{-- Banner Section (MineLab Style with Exact Image) --}}
    <div class="minelab-banner" style="background-image: linear-gradient(to right, rgba(11, 18, 30, 0.96) 0%, rgba(11, 18, 30, 0.88) 45%, rgba(11, 18, 30, 0.35) 75%, rgba(11, 18, 30, 0.12) 100%), url('{{ $hero['image_url'] }}'); background-size: cover; background-position: center right;">
        <div class="minelab-banner-content">
            @if (!empty($hero['badge']))
                <div class="minelab-badge">
                    <span class="minelab-badge-dot"></span>
                    <span>{{ $hero['badge'] }}</span>
                </div>
            @endif
            
            <h1 class="minelab-title">
                {!! nl2br(e($hero['title'])) !!}<br>
                <span>{{ $hero['title_highlight'] }}</span>
            </h1>

            @if (!empty($hero['description']))
                <p class="minelab-desc">
                    {{ $hero['description'] }}
                </p>
            @endif

            <div class="minelab-btn-group">
                @if (!empty($hero['cta_text']))
                    <a class="minelab-btn minelab-btn-primary" href="{{ $hero['cta_url'] ?: '#catalog' }}">
                        <span>{{ $hero['cta_text'] }}</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @endif
                @if (!empty($hero['secondary_text']))
                    <a class="minelab-btn minelab-btn-outline" href="{{ $hero['secondary_url'] ?: route('bonus') }}">
                        <span>{{ $hero['secondary_text'] }}</span>
                    </a>
                @endif
            </div>

            <div class="minelab-trust-features">
                @if (!empty($hero['trust_1']))
                    <div class="trust-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ $hero['trust_1'] }}</span>
                    </div>
                @endif
                @if (!empty($hero['trust_2']))
                    <div class="trust-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ $hero['trust_2'] }}</span>
                    </div>
                @endif
                @if (!empty($hero['trust_3']))
                    <div class="trust-item">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ $hero['trust_3'] }}</span>
                    </div>
                @endif
            </div>
        </div>

        <div class="minelab-banner-media">
            <div class="minelab-art-wrap" id="minelab-hero-slider" data-speed="{{ $hero['slider_speed'] ?? 4 }}" data-direction="{{ $hero['slider_direction'] ?? 'ltr' }}">
                <div class="minelab-art-glow"></div>
                
                {{-- Dynamic Slider Track (Moves from Left to Right) --}}
                <div class="minelab-slides-container">
                    @foreach ($heroImages as $index => $slide)
                        <div class="minelab-slide {{ $index === 0 ? 'is-active' : '' }}" data-slide-index="{{ $index }}">
                            @if (!empty($slide->link_url))
                                <a href="{{ $slide->link_url }}" class="minelab-slide-link">
                            @endif
                            <img
                                src="{{ $slide->imageUrl() }}"
                                alt="{{ $slide->title ?: $hero['title'] }}"
                                class="minelab-art-img"
                                width="600"
                                height="380"
                                loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                @if ($index === 0) fetchpriority="high" @endif
                            >
                            @if (!empty($slide->title))
                                <div class="minelab-slide-caption">
                                    <span>{{ $slide->title }}</span>
                                </div>
                            @endif
                            @if (!empty($slide->link_url))
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Slider Navigation Controls (if more than 1 image) --}}
                @if ($heroImages->count() > 1)
                    <button type="button" class="minelab-slider-btn prev-btn" id="hero-slider-prev" aria-label="Previous slide">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <button type="button" class="minelab-slider-btn next-btn" id="hero-slider-next" aria-label="Next slide">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>

                    <div class="minelab-slider-dots">
                        @foreach ($heroImages as $index => $slide)
                            <button type="button" class="minelab-dot {{ $index === 0 ? 'is-active' : '' }}" data-go-slide="{{ $index }}" aria-label="Slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif

                <!-- Floating Model Badge -->
                <div class="minelab-floating-card">
                    <div class="floating-card-icon">⚡</div>
                    <div class="floating-card-text">
                        <b>TS-50 Flagship</b>
                        <span>51,389 UGX / Day</span>
                    </div>
                    <span class="floating-card-pill">Active</span>
                </div>
            </div>
        </div>
    </div>

    @if ($heroImages->count() > 1)
        {{-- Continuous Dynamic Ribbon Moving Left to Right --}}
        <div class="minelab-ticker-wrap">
            <div class="minelab-ticker-track">
                <div class="minelab-ticker-content minelab-ticker-ltr">
                    @foreach ($heroImages as $slide)
                        <div class="minelab-ticker-item">
                            <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}">
                            <span>{{ $slide->title ?: 'Smart Lock Fleet' }}</span>
                        </div>
                    @endforeach
                    @foreach ($heroImages as $slide)
                        <div class="minelab-ticker-item" aria-hidden="true">
                            <img src="{{ $slide->imageUrl() }}" alt="{{ $slide->title }}">
                            <span>{{ $slide->title ?: 'Smart Lock Fleet' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- MineLab "How Much Will I Earn?" Interactive ROI Calculator --}}
    @if ($hero['show_calculator'])
    @php
        $calcLocks = $allLocks ?? $products;
        $initialLock = $calcLocks->first();
    @endphp
    <div class="minelab-calc-card">
        <div class="calc-head">
            <h3>How Much Will I Earn?</h3>
            <p>Select any smart lock model to calculate your projected daily and 35-day returns</p>
        </div>

        <div class="calc-body">
            <div class="calc-control-group">
                <label for="minelab-lock-select">Select Lock Model</label>
                <div class="calc-select-wrap">
                    <select id="minelab-lock-select" class="calc-select">
                        @foreach ($calcLocks as $lock)
                            @php
                                $lockDays = $lock->purchaseDurationDays();
                                $lockTotal = $lock->daily_income * $lockDays;
                                $lockRoi = round(($lockTotal / max(1, $lock->cost_price)) * 100);
                            @endphp
                            <option
                                value="{{ $lock->id }}"
                                data-name="{{ $lock->name }}"
                                data-cost="{{ $lock->cost_price }}"
                                data-cost-label="{{ $lock->priceLabel() }}"
                                data-daily="{{ $lock->daily_income }}"
                                data-daily-label="{{ $lock->dailyLabel() }}"
                                data-days="{{ $lockDays }}"
                                data-total-label="{{ \App\Support\Money::ugx($lockTotal) }}"
                                data-roi="{{ $lockRoi }}%"
                                data-headline="{{ $lock->inviteHeadline() }}"
                                data-body="{{ $lock->inviteBody() }}"
                                data-image="{{ $lock->imageUrl() }}"
                                data-register-url="{{ route('register', ['lock' => $lock->id]) }}"
                                data-pay-url="{{ route('locks.pay', $lock) }}"
                                {{ $loop->first ? 'selected' : '' }}
                            >
                                {{ $lock->name }} — {{ $lock->priceLabel() }} ({{ $lock->dailyLabel() }}/day)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="calc-metrics-grid">
                <div class="calc-metric-item">
                    <span class="metric-label">Purchase Price</span>
                    <strong id="calc-val-cost" class="metric-val">{{ $initialLock?->priceLabel() }}</strong>
                </div>
                <div class="calc-metric-item">
                    <span class="metric-label">Daily Income</span>
                    <strong id="calc-val-daily" class="metric-val metric-val-accent">{{ $initialLock?->dailyLabel() }}</strong>
                </div>
                <div class="calc-metric-item">
                    <span class="metric-label">Cycle Duration</span>
                    <strong id="calc-val-days" class="metric-val">{{ $initialLock?->purchaseDurationDays() }} Days</strong>
                </div>
                <div class="calc-metric-item calc-metric-highlight">
                    <span class="metric-label">Estimated Revenue (35 Days)</span>
                    <div class="metric-highlight-row">
                        <strong id="calc-val-total" class="metric-val metric-val-gold">
                            {{ $initialLock ? \App\Support\Money::ugx($initialLock->daily_income * $initialLock->purchaseDurationDays()) : '0 UGX' }}
                        </strong>
                        <span id="calc-val-roi" class="roi-pill">
                            {{ $initialLock ? round((($initialLock->daily_income * $initialLock->purchaseDurationDays()) / max(1, $initialLock->cost_price)) * 100) : 0 }}% ROI
                        </span>
                    </div>
                </div>
            </div>

            <div class="calc-action-wrap">
                @if (auth()->guest())
                    <button
                        type="button"
                        id="calc-cta-btn"
                        class="minelab-btn minelab-btn-primary calc-cta"
                        data-signup-lock
                        data-lock-id="{{ $initialLock?->id }}"
                        data-lock-name="{{ $initialLock?->name }}"
                        data-lock-price="{{ $initialLock?->priceLabel() }}"
                        data-lock-daily="{{ $initialLock?->dailyLabel() }}"
                        data-lock-image="{{ $initialLock?->imageUrl() }}"
                        data-lock-headline="{{ $initialLock?->inviteHeadline() }}"
                        data-lock-body="{{ $initialLock?->inviteBody() }}"
                        data-lock-url="{{ route('register', ['lock' => $initialLock?->id]) }}"
                    >
                        <span>Own <span id="calc-cta-name">{{ $initialLock?->name }}</span> & Earn</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </button>
                @else
                    <a
                        id="calc-cta-link"
                        href="{{ $initialLock ? route('locks.pay', $initialLock) : route('products') }}"
                        class="minelab-btn minelab-btn-primary calc-cta"
                    >
                        <span>Own <span id="calc-cta-name">{{ $initialLock?->name }}</span> & Earn</span>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Stats, Live Community Proof, & Quick Actions --}}
    <div class="hero-split minelab-subhero">
        <div class="stat-row">
            <div class="stat"><b>{{ $lockCount }}</b><span>locks in stock</span></div>
            <div class="stat"><b>Daily</b><span>interest per lock</span></div>
            <div class="stat"><b>Any day</b><span>cash out (min 2,000)</span></div>
        </div>
        @include('partials.community-card')
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
