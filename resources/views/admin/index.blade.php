@extends('layouts.app')

@section('title', 'Manager console — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Manager console</p>
    <h1><span>TSL</span> control</h1>
    <div class="head-actions">
        <a class="text-link" href="{{ route('account') }}">My locks</a>
    </div>
    <div class="stat-row three">
        <div class="stat card"><b>{{ $clients->count() }}</b><span>Clients</span></div>
        <div class="stat card"><b>{{ $pendingPurchases->count() }}</b><span>Waiting payment</span></div>
        <div class="stat card"><b>{{ $activeValue }}</b><span>Active value</span></div>
    </div>
</header>

<section class="catalog">
    <div class="section-head">
        <h2>Payment details</h2>
    </div>
    <form method="POST" action="{{ route('admin.payment-methods.update') }}" class="auth-card">
        @csrf
        @method('PUT')
        @foreach ($paymentMethods as $method)
            <fieldset>
                <legend>{{ $method['name'] }}</legend>
                <label>
                    <span>Number / wallet</span>
                    <input class="field-pill" name="{{ $method['key'] }}_number" value="{{ old($method['key'].'_number', $method['number']) }}" required maxlength="30">
                </label>
                <label>
                    <span>Account name</span>
                    <input class="field-pill" name="{{ $method['key'] }}_name" value="{{ old($method['key'].'_name', $method['account_name']) }}" required maxlength="100">
                </label>
            </fieldset>
        @endforeach
        <button class="btn btn-primary" type="submit">Save payment details</button>
    </form>
</section>

<section class="clay-sheet">
    <h2>Payment requests</h2>
    @forelse ($pendingPurchases as $purchase)
        <article class="manage-row pay-row">
            <img src="{{ $purchase->product?->imageUrl() }}" alt="">
            <div>
                <strong>{{ $purchase->payer_name ?: $purchase->user?->profileName() }}</strong>
                <p>{{ collect([$purchase->user?->phone, $purchase->product?->name, $purchase->principalLabel()])->filter()->implode(' · ') }}</p>
                <p class="tx-line">
                    {{ $purchase->paymentMethodLabel() }}
                    @if ($purchase->transaction_id)
                        · TX {{ $purchase->transaction_id }}
                    @endif
                </p>
            </div>
            <div class="row-actions">
                <form method="POST" action="{{ route('admin.activate', $purchase) }}">
                    @csrf
                    <button class="btn btn-small" type="submit">Mark as bought</button>
                </form>
                <form method="POST" action="{{ route('admin.reject', $purchase) }}">
                    @csrf
                    <button class="btn btn-ghost" type="submit">Reject</button>
                </form>
            </div>
        </article>
    @empty
        <p class="sheet-lead">Nothing waiting. All locks are switched on.</p>
    @endforelse
</section>

<section class="catalog">
    <h2>Bonus codes</h2>
    @forelse ($pendingBonuses as $redemption)
        <div class="manage-row card-row">
            <div>
                <strong>{{ $redemption->code }}</strong>
                <p>{{ $redemption->user?->profileName() }}@if ($redemption->user?->phone) · {{ $redemption->user->phone }}@endif</p>
            </div>
            <div class="row-actions">
                <form method="POST" action="{{ route('admin.bonuses.settle', $redemption) }}">
                    @csrf
                    <input type="hidden" name="status" value="applied">
                    <button class="btn btn-small" type="submit">Apply</button>
                </form>
                <form method="POST" action="{{ route('admin.bonuses.settle', $redemption) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button class="btn btn-ghost" type="submit">Reject</button>
                </form>
            </div>
        </div>
    @empty
        <p class="muted">No bonus codes waiting.</p>
    @endforelse
</section>

<section class="catalog">
    <h2>Cash out requests</h2>
    @forelse ($pendingWithdrawals as $withdrawal)
        <div class="manage-row card-row">
            <div>
                <strong>{{ \App\Support\Money::ugx($withdrawal->amount) }}</strong>
                <p>{{ $withdrawal->user?->phone }} · {{ $withdrawal->requested_at->toFormattedDateString() }}</p>
            </div>
            <div class="row-actions">
                <form method="POST" action="{{ route('admin.settle', $withdrawal) }}">
                    @csrf
                    <input type="hidden" name="status" value="paid">
                    <button class="btn btn-small" type="submit">Paid</button>
                </form>
                <form method="POST" action="{{ route('admin.settle', $withdrawal) }}">
                    @csrf
                    <input type="hidden" name="status" value="rejected">
                    <button class="btn btn-ghost" type="submit">Reject</button>
                </form>
            </div>
        </div>
    @empty
        <p class="muted">No pending cash outs.</p>
    @endforelse
</section>

<section class="catalog">
    <div class="section-head">
        <h2>Locks on the shop</h2>
        <a href="{{ route('admin.products.create') }}">Add a lock</a>
    </div>
    <div class="admin-products">
        @foreach ($products as $product)
            <article class="admin-product">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                <div>
                    <strong>{{ $product->name }}</strong>
                    <p>{{ $product->priceLabel() }} · {{ $product->dailyLabel() }}/day</p>
                </div>
                <div class="row-actions">
                    <a class="btn btn-small" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Remove {{ $product->name }} from the shop?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-ghost" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @endforeach
    </div>
</section>

<section class="catalog">
    <div class="section-head">
        <h2>VIP levels</h2>
        <a href="{{ route('admin.products.create', ['kind' => 'vip']) }}">Add a VIP</a>
    </div>
    <div class="admin-products">
        @forelse ($vips as $vip)
            <article class="admin-product">
                <img src="{{ $vip->imageUrl() }}" alt="{{ $vip->name }}">
                <div>
                    <strong>{{ $vip->name }}</strong>
                    <p>{{ $vip->priceLabel() }} recharge · {{ $vip->salaryLabel() }}/month</p>
                </div>
                <div class="row-actions">
                    <a class="btn btn-small" href="{{ route('admin.products.edit', $vip) }}">Edit</a>
                    <form method="POST" action="{{ route('admin.products.destroy', $vip) }}" onsubmit="return confirm('Remove {{ $vip->name }} from the VIP board?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-ghost" type="submit">Delete</button>
                    </form>
                </div>
            </article>
        @empty
            <p class="muted">No VIP levels yet. Add the first one.</p>
        @endforelse
    </div>
</section>

<section class="catalog">
    <div class="section-head">
        <h2>News posts</h2>
        <a href="{{ route('admin.news.create') }}">Write a post</a>
    </div>
    @forelse ($articles as $article)
        <article class="admin-product">
            @if ($article->imageUrl())
                <img src="{{ $article->imageUrl() }}" alt="{{ $article->title }}">
            @else
                @include('partials.avatar', ['person' => $article->author, 'size' => 'md'])
            @endif
            <div>
                <strong>{{ $article->title }}</strong>
                <p>{{ $article->authorName() }} · {{ $article->published_at->diffForHumans() }}</p>
            </div>
            <div class="row-actions">
                <a class="btn btn-small" href="{{ route('admin.news.edit', $article) }}">Edit</a>
                <form method="POST" action="{{ route('admin.news.destroy', $article) }}" onsubmit="return confirm('Remove this post?')">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-ghost" type="submit">Delete</button>
                </form>
            </div>
        </article>
    @empty
        <p class="muted">No posts yet. Add a lock or write a post.</p>
    @endforelse
</section>

<section class="catalog">
    <h2>Clients</h2>
    @foreach ($clients as $client)
        @php
            $own = $purchases->where('user_id', $client->id);
            $earned = $own->sum(fn ($p) => $p->earnedSoFar());
        @endphp
        <details class="client-card">
            <summary>
                <strong>{{ $client->profileName() }}</strong>
                <span>{{ $client->phone ? $client->phone.' · ' : '' }}{{ $own->where('status', 'active')->count() }} active · {{ \App\Support\Money::ugx($earned) }} earned</span>
            </summary>
            <div class="client-body">
                @foreach ($own as $purchase)
                    <div class="mini-lock">
                        <p>
                            <b>{{ $purchase->product?->name }}</b>
                            <small>
                                @if ($purchase->status === 'active')
                                    {{ $purchase->daysLeft() }} days left · {{ \App\Support\Money::ugx($purchase->earnedSoFar()) }} earned
                                @elseif ($purchase->status === 'pending')
                                    Waiting for payment
                                    @if ($purchase->transaction_id)
                                        · TX {{ $purchase->transaction_id }}
                                    @endif
                                @else
                                    {{ ucfirst($purchase->status) }}
                                @endif
                            </small>
                        </p>
                        @if ($purchase->status === 'pending')
                            <form method="POST" action="{{ route('admin.activate', $purchase) }}">
                                @csrf
                                <button class="btn btn-tiny" type="submit">Mark bought</button>
                            </form>
                        @endif
                    </div>
                @endforeach
                <form method="POST" action="{{ route('admin.add-lock', $client) }}" class="add-lock">
                    @csrf
                    <label>Add a lock this client paid for
                        <select name="product_id" onchange="this.form.submit()">
                            <option value="">Choose a lock or VIP…</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} — {{ $product->priceLabel() }}</option>
                            @endforeach
                            @foreach ($vips as $vip)
                                <option value="{{ $vip->id }}">{{ $vip->name }} — {{ $vip->priceLabel() }}</option>
                            @endforeach
                        </select>
                    </label>
                </form>
            </div>
        </details>
    @endforeach
</section>
@endsection
