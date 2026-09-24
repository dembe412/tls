@extends('layouts.app')

@section('title', 'Pay for '.$product->name.' — TSL')
@section('description', 'Send the lock price to the TSL mobile money number, then enter your transaction ID.')

@section('content')
@php
    $tab = 'products';
    $firstMethod = collect($methods)->first();
    $price = (int) $product->cost_price;
    $source = old('payment_source', 'mobile_money');
@endphp

<header class="page-head compact">
    <p class="kicker">Deposit funds</p>
    <h1>Pay for <span>{{ $product->name }}</span></h1>
    <p class="sub">Pay from a balance you already hold, or send mobile money and enter the transaction ID.</p>
</header>

<section class="auth-wrap">
    <div class="auth-card pay-card">
        <div class="intent-lock">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
            <div>
                <strong>{{ $product->name }}</strong>
                <span>{{ $product->priceLabel() }} · {{ $product->dailyLabel() }}/day</span>
            </div>
        </div>

        <form method="POST" action="{{ route('locks.request', $product) }}" class="pay-form" id="pay-form">
            @csrf

            <section class="pay-step">
                <h2><span>1</span> Choose how to pay</h2>
                <div class="pay-methods" data-pay-source>
                    <label class="pay-method">
                        <input type="radio" name="payment_source" value="mobile_money" @checked($source === 'mobile_money') required>
                        <span class="pay-method-body">
                            <strong>Mobile money</strong>
                            <em>Send {{ $product->priceLabel() }} by Airtel or MTN</em>
                        </span>
                    </label>
                    <label class="pay-method">
                        <input
                            type="radio"
                            name="payment_source"
                            value="account"
                            @checked($source === 'account')
                            @disabled($accountBalance < $price)
                        >
                        <span class="pay-method-body">
                            <strong>Account balance</strong>
                            <em>
                                You have {{ \App\Support\Money::ugx($accountBalance) }}
                                @if ($accountBalance < $price) · not enough for this lock @endif
                            </em>
                        </span>
                    </label>
                    <label class="pay-method">
                        <input
                            type="radio"
                            name="payment_source"
                            value="recharge"
                            @checked($source === 'recharge')
                            @disabled($rechargeBalance < $price)
                        >
                        <span class="pay-method-body">
                            <strong>Recharge balance</strong>
                            <em>
                                You have {{ \App\Support\Money::ugx($rechargeBalance) }}
                                @if ($rechargeBalance < $price) · not enough for this lock @endif
                            </em>
                        </span>
                    </label>
                </div>
                <p class="hint">Paying from a balance switches the lock on straight away. Mobile money is switched on once a manager matches your transaction.</p>
            </section>

            <div data-pay-mobile @unless ($source === 'mobile_money') hidden @endunless>
            <section class="pay-step">
                <h2><span>2</span> Choose mobile money provider</h2>
                <div class="pay-methods">
                    @foreach ($methods as $method)
                        <label class="pay-method">
                            <input
                                type="radio"
                                name="payment_method"
                                value="{{ $method['key'] }}"
                                data-name="{{ $method['name'] }}"
                                data-number="{{ $method['number'] }}"
                                data-account="{{ $method['account_name'] }}"
                                @checked(old('payment_method', $firstMethod['key'] ?? '') === $method['key'])
                                required
                            >
                            <span class="pay-method-body">
                                <strong>{{ $method['name'] }}</strong>
                                <em>Send {{ $product->priceLabel() }}</em>
                            </span>
                        </label>
                    @endforeach
                </div>
            </section>

            <section class="pay-step">
                <h2><span>3</span> Payment details</h2>
                <dl class="pay-details">
                    <div>
                        <dt>Provider</dt>
                        <dd data-pay-name>{{ $firstMethod['name'] ?? '' }}</dd>
                    </div>
                    <div>
                        <dt>Number / wallet</dt>
                        <dd class="pay-number">
                            <span data-pay-number>{{ $firstMethod['number'] ?? '' }}</span>
                            <button class="text-link" type="button" data-copy-number>Copy</button>
                        </dd>
                    </div>
                    <div>
                        <dt>Name</dt>
                        <dd data-pay-account>{{ $firstMethod['account_name'] ?? '' }}</dd>
                    </div>
                    <div>
                        <dt>Exact amount</dt>
                        <dd>{{ $product->priceLabel() }}</dd>
                    </div>
                </dl>
                <p class="pay-note">Send the exact amount to the <span data-pay-name>{{ $firstMethod['name'] ?? '' }}</span> number above.</p>
            </section>

            <section class="pay-step">
                <h2><span>4</span> Amount</h2>
                <p class="pay-amount">{{ $product->priceLabel() }}</p>
                <p class="hint">This is the lock price. Send this amount only so the manager can match your payment.</p>
            </section>

            <section class="pay-step">
                <h2><span>5</span> Transaction details</h2>
                <label>
                    <span>Transaction ID <i>*</i></span>
                    <input
                        class="field-pill"
                        name="transaction_id"
                        id="transaction_id"
                        value="{{ old('transaction_id') }}"
                        placeholder="Enter transaction reference number"
                        autocomplete="off"
                        required
                        minlength="4"
                        maxlength="64"
                    >
                </label>
                <p class="hint">Enter the transaction ID or reference from your payment.</p>
            </section>
            </div>

            <section class="pay-step">
                <h2><span>6</span> Payment summary</h2>
                <dl class="pay-details">
                    <div>
                        <dt>Amount</dt>
                        <dd>{{ $product->priceLabel() }}</dd>
                    </div>
                    <div data-pay-mobile @unless ($source === 'mobile_money') hidden @endunless>
                        <dt>Method</dt>
                        <dd data-pay-name>{{ $firstMethod['name'] ?? '' }}</dd>
                    </div>
                    <div data-pay-mobile @unless ($source === 'mobile_money') hidden @endunless>
                        <dt>Transaction ID</dt>
                        <dd data-pay-tx>{{ old('transaction_id') ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt>From</dt>
                        <dd>{{ $user->profileName() }} · {{ $user->phone }}</dd>
                    </div>
                    <div>
                        <dt>To</dt>
                        <dd>TSL Smart Locks</dd>
                    </div>
                </dl>
                <p class="hint">Your name and phone are submitted automatically so the manager can find this payment.</p>

                <label class="pay-confirm" data-pay-mobile @unless ($source === 'mobile_money') hidden @endunless>
                    <input type="checkbox" name="confirmed" value="1" @checked(old('confirmed')) required>
                    <span>I confirm that I have sent the exact amount to the provided number / wallet</span>
                </label>

                <button class="btn btn-primary" type="submit">Submit deposit</button>
            </section>
        </form>
    </div>
</section>
@endsection
