@extends('layouts.app')

@section('title', 'My account — TSL')

@section('content')
@php
    $tab = 'account';
@endphp

<header class="page-head compact">
    <p class="kicker">Welcome back</p>
    <h1>My <span>TSL</span> account</h1>
    <p class="sub">{{ $user->profileName() }}@if ($user->phone) · {{ $user->phone }}@endif · {{ $user->vipRankLabel() }}</p>
    @if ($user->isAdmin())
        <div class="head-actions">
            <a class="text-link" href="{{ route('admin.index') }}">Manager console</a>
        </div>
    @endif
    <div class="stat-row">
        <div class="stat card"><b>{{ $totalRecharge }}</b><span>Total recharge</span></div>
        <div class="stat card"><b>{{ $totalWithdrawn }}</b><span>Total withdraws</span></div>
        <div class="stat card"><b>{{ $dailyEarned }}</b><span>Earned daily</span></div>
    </div>
    <div class="stat-row">
        <div class="stat card"><b>{{ $accountBalance }}</b><span>Account balance</span></div>
        <div class="stat card"><b>{{ $rechargeBalance }}</b><span>Recharge balance</span></div>
    </div>
    <div class="money-actions">
        <a class="btn btn-primary" href="{{ route('products') }}">Recharge</a>
        <a class="btn btn-ghost" href="#withdraw">Withdraw</a>
    </div>
</header>

<section class="acc-panel">
    <article class="acc-block">
        <h2 class="acc-label">Profile</h2>
        <div class="profile-row">
            @include('partials.avatar', ['person' => $user, 'size' => 'md'])
            <div>
                <strong>{{ $user->profileName() }}</strong>
                <p class="muted">
                    {{ $user->phone ?: 'No phone yet' }}
                    · Member since {{ $user->created_at->toFormattedDateString() }}
                    @if ($user->referrer)
                        · Invited by {{ $user->referrer->profileName() }}
                    @endif
                </p>
            </div>
        </div>
        <ul class="acc-facts">
            <li><span>VIP level</span><b>{{ $user->vipRankLabel() }}</b></li>
            <li><span>ABC level</span><b>{{ $abcLevel }}</b></li>
            <li><span>Team</span><b>A {{ $levelA }} · B {{ $levelB }} · C {{ $levelC }}</b></li>
        </ul>
        @if ($vipProgress['next'])
            <p class="acc-note">
                Next card: <strong>{{ $vipProgress['next']->name }}</strong> —
                {{ \App\Support\Money::ugx($vipProgress['recharge']) }} of {{ $vipProgress['next']->priceLabel() }} recharged,
                {{ $vipProgress['members'] }} of {{ $vipProgress['next']->member_requirement }} ABC members.
            </p>
            <div class="vip-progress" role="img" aria-label="{{ $vipProgress['percent'] }} percent towards {{ $vipProgress['next']->name }}">
                <span style="width: {{ $vipProgress['percent'] }}%"></span>
            </div>
            <p class="hint">{{ $vipProgress['percent'] }}% of the way there.</p>
        @else
            <p class="acc-note">You have reached the top VIP card.</p>
        @endif
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Purchased products</h2>
        @forelse ($purchases as $purchase)
            <article class="owner-card">
                <div class="owner-top">
                    <img src="{{ $purchase->product?->imageUrl() ?? asset('images/locks/ts20.svg') }}" alt="">
                    <div>
                        <div class="owner-name">
                            <h3>{{ $purchase->product?->name ?? 'Lock' }}</h3>
                            <span class="pill {{ $purchase->status === 'active' ? 'pill-on' : '' }}">
                                {{ $purchase->status === 'active' ? 'Bought' : 'Awaiting payment' }}
                            </span>
                        </div>
                        <p>
                            {{ $purchase->principalLabel() }}
                            @if ($purchase->product?->isVip())
                                · {{ \App\Support\Money::ugx($purchase->product->monthly_salary) }} / month
                            @else
                                · {{ \App\Support\Money::ugx($purchase->daily_income) }} / day
                            @endif
                        </p>
                        @if ($purchase->status === 'pending' && $purchase->transaction_id)
                            <p class="muted">{{ $purchase->paymentMethodLabel() }} · TX {{ $purchase->transaction_id }}</p>
                        @endif
                        @if ($purchase->status === 'active')
                            <p class="muted">
                                {{ $purchase->isMatured() ? 'Cycle complete — ready to cash out' : $purchase->daysLeft().' of '.$purchase->duration_days.' days left' }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="owner-foot">
                    <div>
                        <p class="muted">Earned so far</p>
                        <strong>{{ \App\Support\Money::ugx($purchase->earnedSoFar()) }}</strong>
                    </div>
                    <form method="POST" action="{{ route('purchases.cash-out', $purchase) }}">
                        @csrf
                        <button
                            class="btn btn-small"
                            type="submit"
                            @disabled(! $purchase->isMatured() || $purchase->availableToCashOut() < $minWithdraw)
                        >
                            {{ $purchase->isMatured() && $purchase->availableToCashOut() <= 0 ? 'Cashed out' : 'Cash out' }}
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <p class="acc-empty">You don’t own a lock yet. <a class="text-link" href="{{ route('products') }}">Pick one from Products</a>, then pay to activate it.</p>
        @endforelse
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Invite link</h2>
        <p class="acc-note">Your invite code is <strong>{{ $user->referral_code }}</strong>. You earn every time someone you invited buys a product.</p>
        <div class="share-box">
            <label class="share-label" for="invite-link">Share this link</label>
            <input class="share-input" id="invite-link" readonly value="{{ $inviteUrl }}">
            <button class="btn btn-share" type="button" data-copy-invite>Copy invite link</button>
        </div>
        <ul class="acc-facts">
            <li><span>Level A</span><b>{{ $referralRates['A']['lock'] }}% locks · {{ $referralRates['A']['vip'] }}% VIP</b></li>
            <li><span>Level B</span><b>{{ $referralRates['B']['lock'] }}%</b></li>
            <li><span>Level C</span><b>{{ $referralRates['C']['lock'] }}%</b></li>
        </ul>
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Referral history</h2>
        <p class="acc-note">{{ $referralTotal }} earned from {{ $invited->count() }} {{ \Illuminate\Support\Str::plural('invite', $invited->count()) }}.</p>
        @if ($referralHistory->isNotEmpty())
            <ul class="acc-list">
                @foreach ($referralHistory as $earning)
                    <li>
                        <span>
                            {{ $earning->amountLabel() }}
                            <small>{{ $earning->levelLabel() }} · {{ $earning->rate_percent }}% from {{ $earning->member?->profileName() }} · {{ $earning->created_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill pill-on">Paid</span>
                    </li>
                @endforeach
            </ul>
        @elseif ($invited->isNotEmpty())
            <ul class="acc-list">
                @foreach ($invited as $member)
                    <li>
                        <span>
                            {{ $member->profileName() }}
                            <small>Joined {{ $member->created_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill">Waiting for their first product</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="acc-empty">Nobody has used your link yet. Share it and you earn when they buy.</p>
        @endif
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Bonuses and rewards</h2>
        <p class="acc-note">{{ $rewardsTotal }} earned from bonuses and referrals.</p>
        @if ($claimedBonuses->isNotEmpty())
            <ul class="acc-list">
                @foreach ($claimedBonuses as $bonus)
                    <li>
                        <span>
                            {{ $bonus->amountLabel() }}
                            <small>{{ $bonus->code }} · {{ $bonus->claimed_at?->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill pill-on">Claimed</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="acc-empty">No bonus codes claimed yet.</p>
        @endif
        <a class="btn btn-ghost" href="{{ route('bonus') }}">Claim a bonus code</a>
    </article>

    <article class="acc-block" id="withdraw">
        <h2 class="acc-label">Withdraw</h2>
        <p class="withdraw-note">
            Minimum withdraw is {{ \App\Support\Money::ugx($minWithdraw) }} according to the local Ugandan instructions that govern the financial regulations.
        </p>
        @if ($withdrawals->isNotEmpty())
            <ul class="acc-list">
                @foreach ($withdrawals as $withdrawal)
                    <li>
                        <span>
                            {{ \App\Support\Money::ugx($withdrawal->amount) }}
                            <small>{{ $withdrawal->reference }} · {{ $withdrawal->requested_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill">{{ $withdrawal->status }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="acc-empty">No cash outs yet. When a lock cycle ends and the amount is at least {{ \App\Support\Money::ugx($minWithdraw) }}, use Cash out on that owner card.</p>
        @endif
    </article>

    @if ($purchases->isNotEmpty())
        <article class="acc-block">
            <h2 class="acc-label">Purchase history</h2>
            <ul class="acc-list">
                @foreach ($purchases as $purchase)
                    <li>
                        <span>
                            {{ $purchase->product?->name ?? 'Lock' }} · {{ $purchase->principalLabel() }}
                            <small>
                                {{ $purchase->paymentMethodLabel() }}
                                @if ($purchase->transaction_id) · {{ $purchase->transaction_id }} @endif
                                · {{ $purchase->created_at->toFormattedDateString() }}
                            </small>
                        </span>
                        <span class="pill {{ $purchase->status === 'active' ? 'pill-on' : '' }}">{{ ucfirst($purchase->status) }}</span>
                    </li>
                @endforeach
            </ul>
        </article>
    @endif

    <article class="acc-block">
        <h2 class="acc-label">Recharge history</h2>
        @if ($rechargeHistory->isNotEmpty())
            <ul class="acc-list">
                @foreach ($rechargeHistory as $entry)
                    <li>
                        <span>
                            {{ $entry->amountLabel() }}
                            <small>{{ $entry->description ?: 'Top-up' }} · {{ $entry->created_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill">{{ $entry->reference ?: 'Top-up' }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="acc-empty">No top-ups yet. A manager adds money to your recharge balance after you send it.</p>
        @endif
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Transaction history</h2>
        @if ($transactions->isNotEmpty())
            <ul class="acc-list">
                @foreach ($transactions as $entry)
                    <li>
                        <span>
                            {{ $entry->amountLabel() }}
                            <small>{{ $entry->walletLabel() }} · {{ $entry->description ?: $entry->type }} · {{ $entry->created_at->toFormattedDateString() }}</small>
                        </span>
                        <span class="pill">{{ $entry->reference ?: $entry->type }}</span>
                    </li>
                @endforeach
            </ul>
        @else
            <p class="acc-empty">No money has moved yet.</p>
        @endif
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Customer support</h2>
        @if ($whatsapp)
            <a class="support-card" href="{{ $whatsapp }}" target="_blank" rel="noopener">
                <span class="support-icon">@include('partials.whatsapp-icon')</span>
                <span class="support-copy">
                    <strong>WhatsApp customer support</strong>
                    <span>Tap to chat with TSL on {{ $whatsappLabel }}</span>
                </span>
            </a>
        @else
            <p class="acc-empty">Support contact is being set up. Check the FAQ meanwhile.</p>
        @endif
        <a class="btn btn-ghost" href="{{ route('faq') }}">Read the FAQ</a>
    </article>

    <article class="acc-block">
        <h2 class="acc-label">Account settings</h2>
        <p class="acc-note">Change your username, phone or photo, then sign in with either one.</p>
        <div class="acc-actions">
            <button class="btn btn-ghost" type="button" data-open-profile>Edit profile</button>
            @if ($user->isAdmin())
                <a class="btn btn-ghost" href="{{ route('security.devices') }}">Registered devices</a>
            @endif
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-ghost btn-danger" type="submit">Log out</button>
            </form>
        </div>
    </article>
</section>
@endsection
