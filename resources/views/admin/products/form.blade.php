@extends('layouts.app')

@php $isVip = old('kind', $product->kind ?: 'lock') === 'vip'; @endphp

@section('title', ($product->exists ? 'Edit '.$product->name : ($isVip ? 'Add a VIP' : 'Add a lock')).' — TSL')

@section('content')
<header class="page-head compact">
    <a class="back" href="{{ route('admin.index') }}">← Manager console</a>
    <p class="kicker">{{ $isVip ? 'VIP board' : 'Catalogue' }}</p>
    <h1>{{ $product->exists ? 'Edit '.$product->name : ($isVip ? 'Add a VIP' : 'Add a lock') }}</h1>
    <p class="sub">
        @if ($isVip)
            Name, colours, recharge, team size, monthly salary, and the lock photo shown on Products.
        @else
            Name, price, the daily interest amount, and the photo shoppers see on Home and Products.
        @endif
    </p>
</header>

<section class="auth-wrap">
    <div class="auth-card product-form">
        <form
            method="POST"
            action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}"
            enctype="multipart/form-data"
            class="auth-form"
        >
            @csrf
            @if ($product->exists)
                @method('PUT')
            @endif
            <input type="hidden" name="kind" value="{{ $isVip ? 'vip' : 'lock' }}">

            <label>
                <span>{{ $isVip ? 'VIP name' : 'Lock name' }}</span>
                <input class="field-pill" name="name" value="{{ old('name', $product->name) }}" placeholder="{{ $isVip ? 'VIP1' : 'TS-60' }}" required>
            </label>
            <label>
                <span>Code</span>
                <input class="field-pill" name="code" value="{{ old('code', $product->code) }}" placeholder="{{ $isVip ? 'vip6' : '1260' }}" required>
            </label>

            @if ($isVip)
                <label>
                    <span>Bar colour</span>
                    <select class="field-pill" name="color" required>
                        @foreach (\App\Models\Product::vipColors() as $key => $label)
                            <option value="{{ $key }}" @selected(old('color', $product->color) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </label>
                <div class="form-row">
                    <label>
                        <span>Cumulative recharge (UGX)</span>
                        <input class="field-pill" type="number" name="cost_price" min="1" value="{{ old('cost_price', $product->cost_price) }}" required>
                    </label>
                    <label>
                        <span>ABC Level members</span>
                        <input class="field-pill" type="number" name="member_requirement" min="0" value="{{ old('member_requirement', $product->member_requirement) }}" required>
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <span>Monthly salary (UGX)</span>
                        <input class="field-pill" type="number" name="monthly_salary" min="1" value="{{ old('monthly_salary', $product->monthly_salary) }}" required>
                    </label>
                    <label>
                        <span>Sort order</span>
                        <input class="field-pill" type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order ?: 0) }}">
                    </label>
                </div>
            @else
                <div class="form-row">
                    <label>
                        <span>Price (UGX)</span>
                        <input class="field-pill" type="number" name="cost_price" min="1" value="{{ old('cost_price', $product->cost_price) }}" required>
                    </label>
                    <label>
                        <span>Daily interest (UGX)</span>
                        <input class="field-pill" type="number" name="daily_income" min="1" value="{{ old('daily_income', $product->daily_income) }}" placeholder="Amount earned each day" required>
                    </label>
                </div>
                <div class="form-row">
                    <label>
                        <span>Cycle days</span>
                        <input class="field-pill" type="number" name="duration_days" min="1" max="365" value="{{ old('duration_days', $product->duration_days ?: 35) }}" required>
                    </label>
                    <label>
                        <span>Sort order</span>
                        <input class="field-pill" type="number" name="sort_order" min="0" value="{{ old('sort_order', $product->sort_order ?: 0) }}">
                    </label>
                </div>
                <label>
                    <span>Short tagline</span>
                    <input class="field-pill" name="tagline" value="{{ old('tagline', $product->tagline) }}" placeholder="The everyday starter lock">
                </label>
            @endif

            <label>
                <span>Lock photo</span>
                <input class="file-input" type="file" name="image" accept="image/jpeg,image/png,image/webp">
                <small class="hint">JPG, PNG or WebP. This is the lock picture on the {{ $isVip ? 'VIP bar' : 'shop' }}.</small>
            </label>
            @if ($product->exists)
                <div class="preview-lock">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}">
                    <span>Current photo</span>
                </div>
            @endif
            <button class="btn btn-primary" type="submit">
                {{ $product->exists ? 'Save' : ($isVip ? 'Add VIP level' : 'Add lock to shop') }}
            </button>
        </form>
    </div>
</section>
@endsection
