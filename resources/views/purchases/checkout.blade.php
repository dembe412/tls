@extends('layouts.app')

@section('title', 'Pay for '.$product->name.' — TSL')
@section('description', 'Send the lock price to the TSL mobile money number, then enter your transaction ID.')

@section('content')
@php
    $tab = 'products';
    $firstMethod = collect($methods)->first();
@endphp

<header class="page-head compact">
    <p class="kicker">Deposit funds</p>
    <h1>Pay for <span>{{ $product->name }}</span></h1>
    <p class="sub">Send the exact amount to the number below, then enter the transaction ID. Your name is attached automatically.</p>
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
                <h2><span>1</span> Choose payment method</h2>
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
                <h2><span>2</span> Payment details</h2>
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
                <h2><span>3</span> Amount</h2>
                <p class="pay-amount">{{ $product->priceLabel() }}</p>
                <p class="hint">This is the lock price. Send this amount only so the manager can match your payment.</p>
            </section>

            <section class="pay-step">
                <h2><span>4</span> Transaction details</h2>
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

            <section class="pay-step">
                <h2><span>5</span> Payment summary</h2>
                <dl class="pay-details">
                    <div>
                        <dt>Amount</dt>
                        <dd>{{ $product->priceLabel() }}</dd>
                    </div>
                    <div>
                        <dt>Method</dt>
                        <dd data-pay-name>{{ $firstMethod['name'] ?? '' }}</dd>
                    </div>
                    <div>
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

                <label class="pay-confirm">
                    <input type="checkbox" name="confirmed" value="1" @checked(old('confirmed')) required>
                    <span>I confirm that I have sent the exact amount to the provided number / wallet</span>
                </label>

                <button class="btn btn-primary" type="submit">Submit deposit</button>
            </section>
        </form>
    </div>
</section>
@endsection
