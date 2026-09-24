@extends('layouts.app')

@section('title', 'Approve sign-in — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">{{ config('security.org_name') }}</p>
    <h1>Waiting for device approval</h1>
    <p class="sub">A login request was sent to your registered browser. Approve it there. This request expires soon and can be used only once.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card" id="challenge-wait" data-status-url="{{ route('security.challenge.status', $challenge) }}" data-complete-url="{{ route('security.challenge.complete', $challenge) }}">
        <p class="auth-lead">Status: <strong>{{ $challenge->status }}</strong></p>
        @if ($device)
            <p class="hint">This browser is registered. You can approve here.</p>
            <form method="POST" action="{{ route('security.challenge.decide', $challenge) }}" class="money-actions">
                @csrf
                <button class="btn btn-primary" name="decision" value="approved">Approve</button>
                <button class="btn btn-ghost" name="decision" value="rejected">Reject</button>
            </form>
        @endif
    </div>
</section>
@endsection
