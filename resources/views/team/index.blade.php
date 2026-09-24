@extends('layouts.app')

@section('title', 'Team — TSL')
@section('description', 'Invite your team, earn referral commission, and see VIP levels.')

@section('content')
<header class="page-head compact">
    <p class="kicker">Grow together</p>
    <h1>Your <span>TSL</span> team</h1>
    <p class="sub">Invite people with your link. You earn a share when they buy a product.</p>
    @if ($user)
        <div class="stat-row">
            <div class="stat card"><b>{{ $levelA }}</b><span>Level A</span></div>
            <div class="stat card"><b>{{ $levelB }}</b><span>Level B</span></div>
            <div class="stat card"><b>{{ $levelC }}</b><span>Level C</span></div>
        </div>
    @endif
</header>

<section class="clay-sheet">
    <div class="sheet-title">
        <h2>Referrals</h2>
    </div>
    <ul class="level-list">
        <li><strong>Level A</strong><span>{{ $rates['A']['lock'] }}% locks · {{ $rates['A']['vip'] }}% VIP</span></li>
        <li><strong>Level B</strong><span>{{ $rates['B']['lock'] }}% commission</span></li>
        <li><strong>Level C</strong><span>{{ $rates['C']['lock'] }}% commission</span></li>
    </ul>
    <p class="hint">Commission lands in your account balance the moment their product is switched on.</p>
    @auth
        <p class="sheet-lead">Your invite code is <strong>{{ $user->referral_code }}</strong>.</p>
        <div class="share-box">
            <label class="share-label" for="invite-link">Share this link</label>
            <input class="share-input" id="invite-link" readonly value="{{ $inviteUrl }}">
            <button class="btn btn-share" type="button" data-copy-invite>Copy invite link</button>
        </div>
    @else
        <p class="sheet-lead">Create an account to get your invite link.</p>
        <a class="btn btn-primary" href="{{ route('register') }}">Create a free account</a>
    @endauth
</section>

@if ($vips->isNotEmpty())
    @include('partials.vip-board', ['vips' => $vips])
@endif
@endsection
