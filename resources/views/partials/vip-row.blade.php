@php
    $guest = auth()->guest();
    $owned = ! $guest && auth()->user()->hasOpenLock($vip);
    $level = max(1, min(6, (int) ($vip->sort_order ?: 1)));
    $classes = 'vip-card vip-lvl-'.$level;
@endphp
@if ($guest)
    <button
        type="button"
        class="{{ $classes }}"
        data-signup-lock
        data-lock-id="{{ $vip->id }}"
        data-lock-name="{{ $vip->name }}"
        data-lock-price="{{ $vip->priceLabel() }}"
        data-lock-daily="{{ $vip->salaryLabel() }}"
        data-lock-image="{{ $vip->imageUrl() }}"
        data-lock-headline="{{ $vip->inviteHeadline() }}"
        data-lock-body="{{ $vip->inviteBody() }}"
        data-lock-url="{{ route('register', ['lock' => $vip->id]) }}"
    >
        @include('partials.vip-card-body', ['vip' => $vip])
    </button>
@elseif ($owned)
    <a class="{{ $classes }}" href="{{ route('account') }}">
        @include('partials.vip-card-body', ['vip' => $vip])
    </a>
@else
    <a class="{{ $classes }}" href="{{ route('locks.pay', $vip) }}">
        @include('partials.vip-card-body', ['vip' => $vip])
    </a>
@endif
