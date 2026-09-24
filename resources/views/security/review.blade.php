@extends('layouts.app')

@section('title', 'Approve request — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">{{ config('security.org_name') }}</p>
    <h1>
        @if ($challenge->type === 'withdrawal')
            Withdrawal authorization requested.
        @else
            Login request
        @endif
    </h1>
    <p class="sub">
        @if ($challenge->type === 'withdrawal')
            Approve this transaction from this registered browser only.
        @else
            {{ $challenge->context['summary'] ?? 'Another device asked to sign in.' }}
        @endif
    </p>
</header>

<section class="auth-wrap">
    <div class="auth-card">
        @if ($challenge->type === 'withdrawal')
            <p class="pay-amount">{{ $amount }}</p>
            <p class="hint">Reference {{ $reference }}</p>
        @endif
        <p class="auth-lead">Status: <strong>{{ $challenge->status }}</strong> · device {{ $device->name }}</p>
        @if ($challenge->isPending())
            <form method="POST" action="{{ route('security.challenge.decide', $challenge) }}" class="money-actions">
                @csrf
                <button class="btn btn-primary" name="decision" value="approved">Approve</button>
                <button class="btn btn-ghost" name="decision" value="rejected">Reject</button>
            </form>
        @endif
    </div>
</section>
@endsection
