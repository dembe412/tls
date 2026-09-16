@extends('layouts.app')

@section('title', 'Sign in to TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Welcome back</p>
    <h1>Sign in to continue</h1>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        <h2>Sign in</h2>
        <p class="auth-lead">Use your username or phone number, plus your password.</p>
        <form method="POST" action="{{ route('login') }}" class="auth-form">
            @csrf
            <label>
                <span>Username or phone</span>
                <input class="field-pill" name="login" value="{{ old('login') }}" placeholder="Name or 07XX XXX XXX" autocomplete="username" required>
            </label>
            <label>
                <span>Password</span>
                <input class="field-pill" type="password" name="password" required placeholder="Your password">
            </label>
            <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="fine">New here? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</section>
@endsection
