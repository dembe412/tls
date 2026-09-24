@extends('layouts.app')

@section('title', 'Register this browser — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Trusted device</p>
    <h1>Register this browser</h1>
    <p class="sub">{{ $user->profileName() }} · Only a registered browser can approve logins and withdrawals. Open the manager console to see requests waiting for a decision.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        <form method="POST" action="{{ route('security.devices.store') }}" class="auth-form">
            @csrf
            <label>
                <span>Device name</span>
                <input class="field-pill" name="name" value="{{ old('name', 'Office browser') }}" required maxlength="80">
            </label>
            <p class="hint">Keep this browser. Requests waiting for approval are listed in the manager console.</p>
            <button class="btn btn-primary" type="submit">Register this browser</button>
        </form>
    </div>
</section>
@endsection
