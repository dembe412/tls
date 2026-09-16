@extends('layouts.app')

@section('title', 'Redeem bonus — TSL')
@section('description', 'Enter a TSL bonus code so the manager can apply it to your account.')

@section('content')
<header class="page-head compact">
    <p class="kicker">Rewards</p>
    <h1>Redeem bonus</h1>
    <p class="sub">Enter the code TSL sent you. The manager checks it, then applies it to your account.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        @auth
            <h2>Bonus code</h2>
            <p class="auth-lead">Letters, numbers or dashes. One code per member.</p>
            <form method="POST" action="{{ route('bonus.redeem') }}" class="auth-form">
                @csrf
                <label>
                    <span>Code</span>
                    <input class="field-pill" name="code" value="{{ old('code') }}" placeholder="TSL-XXXX" autocomplete="off" required>
                </label>
                <button class="btn btn-primary" type="submit">Send bonus code</button>
            </form>
        @else
            <h2>Sign in to redeem</h2>
            <p class="auth-lead">Bonus codes are tied to your TSL account. Sign in or create one, then come back here.</p>
            <a class="btn btn-primary" href="{{ route('login') }}">Sign in</a>
            <p class="fine">New here? <a href="{{ route('register') }}">Create an account</a></p>
        @endauth
    </div>
</section>

@auth
    @if ($redemptions->isNotEmpty())
        <section class="catalog">
            <h2>Your codes</h2>
            <ul class="plain-list">
                @foreach ($redemptions as $redemption)
                    <li>
                        <span>
                            {{ $redemption->code }}
                            <small>{{ $redemption->created_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill">{{ $redemption->statusLabel() }}</span>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
@endauth
@endsection
