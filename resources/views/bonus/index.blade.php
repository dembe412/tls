@extends('layouts.app')

@section('title', 'Claim bonus — TSL')
@section('description', 'Enter the TSL bonus code your manager sent you and the money lands in your account balance.')

@section('content')
<header class="page-head compact">
    <p class="kicker">Rewards</p>
    <h1>Claim a bonus</h1>
    <p class="sub">Enter the code a manager sent you. Claim it quickly — a bonus code expires {{ $bonusMinutes }} minutes after it is made.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        @auth
            <h2>Bonus code</h2>
            <p class="auth-lead">The money goes straight into your account balance.</p>
            <form method="POST" action="{{ route('bonus.redeem') }}" class="auth-form">
                @csrf
                <label>
                    <span>Code</span>
                    <input class="field-pill" name="code" value="{{ old('code') }}" placeholder="TSLXXXXXXX" autocomplete="off" required>
                </label>
                <button class="btn btn-primary" type="submit">Claim bonus</button>
            </form>
        @else
            <h2>Sign in to claim</h2>
            <p class="auth-lead">Bonus codes are tied to your TSL account. Sign in or create one, then come back here.</p>
            <a class="btn btn-primary" href="{{ route('login') }}">Sign in</a>
            <p class="fine">New here? <a href="{{ route('register') }}">Create an account</a></p>
        @endauth
    </div>
</section>

@auth
    @if ($claimed->isNotEmpty())
        <section class="catalog">
            <h2>Bonuses you claimed</h2>
            <ul class="plain-list">
                @foreach ($claimed as $bonus)
                    <li>
                        <span>
                            {{ $bonus->amountLabel() }}
                            <small>{{ $bonus->code }} · {{ $bonus->claimed_at?->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill pill-on">Claimed</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endauth
@endsection
