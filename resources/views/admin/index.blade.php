@extends('layouts.app')

@section('title', 'Manager console — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Manager console</p>
    <h1><span>TSL</span> control</h1>
    <div class="head-actions">
        <a class="text-link" href="{{ route('account') }}">My locks</a>
        <a class="text-link" href="{{ route('admin.hero.index') }}">Hero slider & images</a>
        <a class="text-link" href="{{ route('security.devices') }}">Devices</a>
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
        <label>
            <span>WhatsApp customer support</span>
            <input class="field-pill" name="whatsapp" value="{{ old('whatsapp', $whatsapp) }}" placeholder="2567XXXXXXXX" maxlength="30">
        </label>
        <button class="btn btn-primary" type="submit">Save payment details</button>
    </form>
</section>

<section class="catalog">
    <div class="section-head">
        <h2>Hero section & Dynamic images</h2>
        <a class="text-link" href="{{ route('admin.hero.index') }}">Open dedicated page →</a>
    </div>
    <div class="auth-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; padding: 1.25rem 1.5rem; background: linear-gradient(135deg, rgba(239, 138, 44, 0.08) 0%, rgba(11, 18, 30, 0.6) 100%); border: 1px solid var(--accent); border-radius: 1rem; margin-bottom: 1.5rem;">
        <div>
            <strong style="font-size: 1.05rem; display: block; margin-bottom: 0.25rem;">🖼️ Dynamic Hero Image Slider (Moving Left to Right)</strong>
            <p class="muted" style="margin: 0;">Upload multiple images, toggle active slides, set slider speed, adjust headlines, or customize ROI calculator.</p>
        </div>
        <a class="btn btn-primary" href="{{ route('admin.hero.index') }}">
            Manage Hero Slider & Images →
        </a>
    </div>
</section>

<section class="catalog">
    <div class="section-head">
        <h2>Hero section quick appearance</h2>
    </div>
    <form method="POST" action="{{ route('admin.hero-settings.update') }}" enctype="multipart/form-data" class="auth-card">
        @csrf
        @method('PUT')

        <fieldset>
            <legend>Hero banner image</legend>
            <div style="margin-bottom: 0.85rem;">
                <p class="muted" style="margin-bottom: 0.5rem;">Current hero image:</p>
                <img src="{{ $hero['image_url'] }}" alt="Current Hero Image" style="width: 100%; max-width: 320px; height: auto; border-radius: 0.85rem; border: 1px solid var(--line); box-shadow: var(--shadow); object-fit: cover;">
            </div>
            <label>
                <span>Upload new hero image (JPG, PNG, WEBP)</span>
                <input class="field-pill" type="file" name="image" accept="image/*">
            </label>
            @if ($hero['image_path'])
                <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="remove_image" value="1">
                    <span>Reset to MineLab default image</span>
                </label>
            @endif
        </fieldset>

        <fieldset>
            <legend>Headlines & Copy</legend>
            <label>
                <span>Badge / Kicker text</span>
                <input class="field-pill" name="badge" value="{{ old('badge', $hero['badge']) }}" placeholder="TUYA SMART HARDWARE · DAILY YIELD FLEET" maxlength="120">
            </label>
            <label>
                <span>Primary headline</span>
                <input class="field-pill" name="title" value="{{ old('title', $hero['title']) }}" required placeholder="ACHIEVE THE HIGHEST" maxlength="150">
            </label>
            <label>
                <span>Highlighted text (Gold Gradient)</span>
                <input class="field-pill" name="title_highlight" value="{{ old('title_highlight', $hero['title_highlight']) }}" placeholder="DAILY LOCK YIELD" maxlength="150">
            </label>
            <label>
                <span>Description paragraph</span>
                <textarea class="field-pill" name="description" rows="3" maxlength="1000" style="height: auto; border-radius: 1rem; padding: 0.75rem 1rem;">{{ old('description', $hero['description']) }}</textarea>
            </label>
        </fieldset>

        <fieldset>
            <legend>Call to Action Buttons</legend>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <label>
                    <span>Primary button text</span>
                    <input class="field-pill" name="cta_text" value="{{ old('cta_text', $hero['cta_text']) }}" placeholder="Start Earning" maxlength="60">
                </label>
                <label>
                    <span>Primary button link</span>
                    <input class="field-pill" name="cta_url" value="{{ old('cta_url', $hero['cta_url']) }}" placeholder="#catalog" maxlength="255">
                </label>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                <label>
                    <span>Secondary button text</span>
                    <input class="field-pill" name="secondary_text" value="{{ old('secondary_text', $hero['secondary_text']) }}" placeholder="Redeem Bonus 🎁" maxlength="60">
                </label>
                <label>
                    <span>Secondary button link</span>
                    <input class="field-pill" name="secondary_url" value="{{ old('secondary_url', $hero['secondary_url']) }}" placeholder="{{ route('bonus') }}" maxlength="255">
                </label>
            </div>
        </fieldset>

        <fieldset>
            <legend>Trust Badges & Options</legend>
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.6rem;">
                <label>
                    <span>Trust point 1</span>
                    <input class="field-pill" name="trust_1" value="{{ old('trust_1', $hero['trust_1']) }}" placeholder="35-Day Cashout" maxlength="80">
                </label>
                <label>
                    <span>Trust point 2</span>
                    <input class="field-pill" name="trust_2" value="{{ old('trust_2', $hero['trust_2']) }}" placeholder="Daily Automated Payouts" maxlength="80">
                </label>
                <label>
                    <span>Trust point 3</span>
                    <input class="field-pill" name="trust_3" value="{{ old('trust_3', $hero['trust_3']) }}" placeholder="Min Withdraw: 2,000 UGX" maxlength="80">
                </label>
            </div>
            <label style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.75rem; cursor: pointer;">
                <input type="checkbox" name="show_calculator" value="1" {{ old('show_calculator', $hero['show_calculator']) ? 'checked' : '' }}>
                <span>Show "How Much Will I Earn?" Interactive ROI Calculator</span>
            </label>
        </fieldset>

        <button class="btn btn-primary" type="submit">Save hero section</button>
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
    <p class="muted">Create a code, then send the link to the member. It expires after {{ $bonusMinutes }} minutes if nobody claims it.</p>
    <form method="POST" action="{{ route('admin.bonuses.store') }}" class="add-lock">
        @csrf
        <label>Amount (UGX)
            <input class="field-pill" type="number" name="amount" min="1" placeholder="10000" required>
        </label>
        <label>Send to
            <select name="assigned_user_id">
                <option value="">Anyone with the link</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->profileName() }}@if ($client->phone) · {{ $client->phone }}@endif</option>
                @endforeach
            </select>
        </label>
        <label>Note
            <input class="field-pill" name="note" maxlength="120" placeholder="Weekend reward">
        </label>
        <button class="btn btn-tiny" type="submit">Create bonus code</button>
    </form>

    @forelse ($bonusCodes as $bonus)
        <div class="manage-row card-row">
            <div>
                <strong>{{ $bonus->code }} · {{ $bonus->amountLabel() }}</strong>
                <p>
                    {{ $bonus->assignedTo?->profileName() ?? 'Anyone with the link' }}
                    @if ($bonus->isClaimed())
                        · claimed by {{ $bonus->claimedBy?->profileName() }} {{ $bonus->claimed_at->diffForHumans() }}
                    @elseif ($bonus->hasExpired())
                        · expired {{ $bonus->expires_at->diffForHumans() }}
                    @else
                        · expires {{ $bonus->expires_at->diffForHumans() }}
                    @endif
                </p>
                @if ($bonus->isOpen())
                    <input class="field-pill" readonly value="{{ $bonus->shareUrl() }}">
                @endif
            </div>
            <span class="pill">{{ $bonus->statusLabel() }}</span>
        </div>
    @empty
        <p class="muted">No bonus codes yet.</p>
    @endforelse
</section>

<section class="catalog">
    <h2>Waiting for device approval</h2>
    @forelse ($pendingApprovals as $challenge)
        <div class="manage-row card-row">
            <div>
                <strong>
                    @if ($challenge->type === 'withdrawal')
                        {{ $challenge->context['amount_label'] ?? 'Withdrawal' }}
                    @else
                        Login request
                    @endif
                </strong>
                <p>
                    {{ $challenge->context['reference'] ?? $challenge->context['member'] ?? $challenge->user?->profileName() }}
                    · expires {{ $challenge->expires_at->diffForHumans() }}
                </p>
            </div>
            <div class="row-actions">
                <a class="btn btn-small" href="{{ route('security.review', $challenge) }}">Review</a>
            </div>
        </div>
    @empty
        <p class="muted">Nothing waiting for approval.</p>
    @endforelse
</section>

<section class="catalog">
    <h2>Cash out requests</h2>
    @forelse ($pendingWithdrawals as $withdrawal)
        <div class="manage-row card-row">
            <div>
                <strong>{{ \App\Support\Money::ugx($withdrawal->amount) }}</strong>
                <p>
                    {{ $withdrawal->reference }}
                    · {{ $withdrawal->status }}
                    · {{ $withdrawal->user?->phone }}
                    · {{ $withdrawal->requested_at->toFormattedDateString() }}
                </p>
            </div>
            <div class="row-actions">
                @if (in_array($withdrawal->status, ['authorized', 'pending', 'awaiting_approval'], true))
                <form method="POST" action="{{ route('admin.settle', $withdrawal) }}">
                    @csrf
                    <input type="hidden" name="status" value="paid">
                    <button class="btn btn-small" type="submit">Paid</button>
                </form>
                @endif
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
                <p class="muted">
                    Account balance {{ \App\Support\Money::ugx($client->accountBalance()) }}
                    · Recharge balance {{ \App\Support\Money::ugx($client->rechargeBalance()) }}
                </p>
                <form method="POST" action="{{ route('admin.credit', $client) }}" class="add-lock">
                    @csrf
                    <label>Add money to a balance
                        <select name="wallet">
                            <option value="recharge">Recharge balance</option>
                            <option value="account">Account balance</option>
                        </select>
                    </label>
                    <label>Amount (UGX)
                        <input class="field-pill" type="number" name="amount" min="1" placeholder="50000" required>
                    </label>
                    <button class="btn btn-tiny" type="submit">Add money</button>
                </form>
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
