@extends('layouts.app')

@section('title', 'Create your TSL account')

@section('content')
<header class="page-head compact">
    <p class="kicker">Join TSL</p>
    <h1>{{ $inviteHeadline }}</h1>
    @if ($product)
        <p class="sub">{{ $inviteBody }}</p>
    @endif
</header>

<section class="auth-wrap">
    <div class="auth-card">
        @if ($product)
            <div class="intent-lock">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                <div>
                    <strong>{{ $product->name }}</strong>
                    <span>{{ $product->priceLabel() }} · {{ $product->dailyLabel() }}/day</span>
                </div>
            </div>
        @endif
        <h2>Create account</h2>
        <p class="auth-lead">Use a username, a phone number, or both. No SMS code is sent — just confirm you are not a robot below.</p>
        <form method="POST" action="{{ route('register') }}" class="auth-form">
            @csrf
            @if ($product)
                <input type="hidden" name="product_id" value="{{ $product->id }}">
            @endif
            @if ($referrer)
                <input type="hidden" name="ref" value="{{ $referrer->referral_code }}">
                <p class="invite-chip">Invited by {{ $referrer->profileName() }}</p>
            @elseif (old('ref'))
                <input type="hidden" name="ref" value="{{ old('ref') }}">
            @endif
            <label>
                <span>Username</span>
                <input class="field-pill" name="name" value="{{ old('name') }}" placeholder="Your name" autocomplete="username">
            </label>
            <label>
                <span>Phone number</span>
                <input class="field-pill" name="phone" inputmode="tel" value="{{ old('phone') }}" placeholder="07XX XXX XXX">
            </label>
            <label>
                <span>Password</span>
                <input class="field-pill" type="password" name="password" required minlength="6" placeholder="At least 6 characters">
            </label>
            <label>
                <span>Confirm password</span>
                <input class="field-pill" type="password" name="password_confirmation" required minlength="6" placeholder="Repeat password">
            </label>
            @include('partials.human-check')
            <button class="btn btn-primary" type="submit">Create account</button>
        </form>
        <p class="fine">Already have an account? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</section>
@endsection
