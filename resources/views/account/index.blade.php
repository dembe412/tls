@extends('layouts.app')

@section('title', 'My locks — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Welcome back</p>
    <h1>My <span>TSL</span> locks</h1>
    <p class="sub">{{ $user->profileName() }}@if ($user->phone) · {{ $user->phone }}@endif</p>
    @if ($user->isAdmin())
        <div class="head-actions">
            <a class="text-link" href="{{ route('admin.index') }}">Manager console</a>
        </div>
    @endif
    <div class="stat-row">
        <div class="stat card"><b>{{ $totalEarned }}</b><span>Total earned</span></div>
        <div class="stat card"><b>{{ $activeValue }}</b><span>Active value</span></div>
    </div>
</header>

<section class="clay-sheet">
    <div class="sheet-title">
        <h2>Owner card</h2>
        @include('partials.paw', ['class' => 'paw paw-mini'])
    </div>

    @forelse ($purchases as $purchase)
        <article class="owner-card">
            <div class="owner-top">
                <img src="{{ $purchase->product?->imageUrl() ?? asset('images/locks/ts20.svg') }}" alt="">
                <div>
                    <div class="owner-name">
                        <h3>{{ $purchase->product?->name ?? 'Lock' }}</h3>
                        <span class="pill {{ $purchase->status === 'active' ? 'pill-on' : '' }}">
                            {{ $purchase->status === 'active' ? 'Bought' : 'Awaiting payment' }}
                        </span>
                    </div>
                    <p>
                        {{ $purchase->principalLabel() }}
                        @if ($purchase->product?->isVip())
                            · {{ \App\Support\Money::ugx($purchase->product->monthly_salary) }} / month
                        @else
                            · {{ \App\Support\Money::ugx($purchase->daily_income) }} / day
                        @endif
                    </p>
                    @if ($purchase->status === 'pending' && $purchase->transaction_id)
                        <p class="muted">{{ $purchase->paymentMethodLabel() }} · TX {{ $purchase->transaction_id }}</p>
                    @endif
                    @if ($purchase->status === 'active')
                        <p class="muted">
                            {{ $purchase->isMatured() ? 'Cycle complete — ready to cash out' : $purchase->daysLeft().' of '.$purchase->duration_days.' days left' }}
                        </p>
                    @endif
                </div>
            </div>
            <div class="owner-foot">
                <div>
                    <p class="muted">Earned so far</p>
                    <strong>{{ \App\Support\Money::ugx($purchase->earnedSoFar()) }}</strong>
                </div>
                <form method="POST" action="{{ route('purchases.cash-out', $purchase) }}">
                    @csrf
                    <button
                        class="btn btn-small"
                        type="submit"
                        @disabled(! $purchase->isMatured() || $purchase->availableToCashOut() <= 0)
                    >
                        {{ $purchase->isMatured() && $purchase->availableToCashOut() <= 0 ? 'Cashed out' : 'Cash out' }}
                    </button>
                </form>
            </div>
        </article>
    @empty
        <p class="sheet-lead">You don’t own a lock yet. Pick one below and send the money to activate it.</p>
    @endforelse
</section>

<section class="catalog">
    <div class="section-head">
        <h2>Other locks available</h2>
        <p>Each lock pays the daily amount shown on its card.</p>
    </div>
    <div class="lock-grid">
        @foreach ($products as $product)
            @include('partials.lock-card', ['product' => $product])
        @endforeach
    </div>
</section>

@if ($vips->isNotEmpty())
    @include('partials.vip-board', ['vips' => $vips])
@endif

@if ($withdrawals->isNotEmpty())
<section class="catalog">
    <h2>Cash outs</h2>
    <ul class="plain-list">
        @foreach ($withdrawals as $withdrawal)
            <li>
                <span>
                    {{ \App\Support\Money::ugx($withdrawal->amount) }}
                    <small>{{ $withdrawal->requested_at->toFormattedDateString() }}</small>
                </span>
                <span class="pill">{{ $withdrawal->status }}</span>
            </li>
        @endforeach
    </ul>
</section>
@endif
@endsection
