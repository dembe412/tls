@extends('layouts.app')

@section('title', 'Your TSL bonus')
@section('description', 'A TSL manager sent you a bonus. Claim it before it expires.')

@section('content')
<header class="page-head compact">
    <p class="kicker">Rewards</p>
    <h1>A bonus is waiting</h1>
    <p class="sub">{{ $bonus->note ?: 'A TSL manager sent you this bonus.' }}</p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        <p class="pay-amount">{{ $bonus->amountLabel() }}</p>
        <p class="hint">Code {{ $bonus->code }}</p>

        @if ($bonus->isClaimed())
            <p class="auth-lead">This bonus was already claimed{{ $bonus->claimedBy ? ' by '.$bonus->claimedBy->profileName() : '' }}.</p>
            <a class="btn btn-ghost" href="{{ route('bonus') }}">Back to bonuses</a>
        @elseif ($bonus->hasExpired())
            <p class="auth-lead">This bonus expired {{ $bonus->expires_at->diffForHumans() }}. Ask the manager to send a new one.</p>
            <a class="btn btn-ghost" href="{{ route('bonus') }}">Back to bonuses</a>
        @elseif (! $user)
            <p class="auth-lead">Sign in to claim it before it expires {{ $bonus->expires_at->diffForHumans() }}.</p>
            <a class="btn btn-primary" href="{{ route('login') }}">Sign in and claim</a>
            <p class="fine">New here? <a href="{{ route('register') }}">Create an account</a></p>
        @elseif (! $bonus->isFor($user))
            <p class="auth-lead">This bonus was sent to another member.</p>
            <a class="btn btn-ghost" href="{{ route('bonus') }}">Back to bonuses</a>
        @else
            <p class="auth-lead">Claim it before it expires {{ $bonus->expires_at->diffForHumans() }}. It goes into your account balance.</p>
            <form method="POST" action="{{ route('bonus.redeem') }}" class="auth-form">
                @csrf
                <input type="hidden" name="code" value="{{ $bonus->code }}">
                <button class="btn btn-primary" type="submit">Claim {{ $bonus->amountLabel() }}</button>
            </form>
        @endif
    </div>
</section>
@endsection
