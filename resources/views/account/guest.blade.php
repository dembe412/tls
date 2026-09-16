@extends('layouts.app')

@section('title', 'My TSL account')

@section('content')
@php $tab = 'account'; @endphp

<header class="page-head">
    <p class="kicker">Members area</p>
    <h1>Your locks live here</h1>
    <p class="sub">Create a free account to request a lock, watch your daily interest grow, and cash out on day 35.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        <h2>Sign up first</h2>
        <p class="auth-lead">It takes twenty seconds. Then every lock on the home page is yours to request.</p>
        <ul class="perks">
            <li>Owner card for every lock you buy</li>
            <li>Daily earnings you can actually see</li>
            <li>Cash-out button when the cycle ends</li>
        </ul>
        <a class="btn btn-primary" href="{{ route('register') }}">Create a free account</a>
        <p class="fine">Already with us? <a href="{{ route('login') }}">Sign in</a></p>
    </div>
</section>
@endsection
