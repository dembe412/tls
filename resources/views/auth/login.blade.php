@extends('layouts.app')

@section('title', 'Sign in to TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Welcome back</p>
    <h1>Sign in to continue</h1>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        <p class="auth-lead">Sign in with your username or phone number and password.</p>
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
            <label class="keep-in">
                <input type="checkbox" name="keep_signed_in" value="1" @checked(old('keep_signed_in'))>
                <span>Keep me signed in on this device</span>
            </label>
            <button class="btn btn-primary" type="submit">Sign in</button>
        </form>
        <p class="fine">New here? <a href="{{ route('register') }}">Create an account</a></p>
    </div>
</section>
@endsection
