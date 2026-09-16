@php
    $guest = auth()->guest();
    $owned = ! $guest && auth()->user()->hasOpenLock($product);
    $incomeLine = $product->isVip()
        ? $product->salaryLabel().' / month'
        : $product->dailyLabel().' / day';
    $incomeValue = $product->isVip() ? $product->salaryLabel() : $product->dailyLabel();
@endphp
<article class="lock-card{{ $product->isVip() ? ' lock-card-gold' : '' }}">
    @if ($guest)
        <button
            type="button"
            class="lock-hit"
            data-signup-lock
            data-lock-id="{{ $product->id }}"
            data-lock-name="{{ $product->name }}"
            data-lock-price="{{ $product->priceLabel() }}"
            data-lock-daily="{{ $incomeValue }}"
            data-lock-image="{{ $product->imageUrl() }}"
            data-lock-headline="{{ $product->inviteHeadline() }}"
            data-lock-body="{{ $product->inviteBody() }}"
            data-lock-url="{{ route('register', ['lock' => $product->id]) }}"
        >
            @include('partials.lock-card-face', ['product' => $product, 'incomeLine' => $incomeLine, 'chip' => $product->isVip() ? 'VIP' : 'Tap to own'])
        </button>
    @elseif ($owned)
        <a class="lock-hit" href="{{ route('account') }}">
            @include('partials.lock-card-face', ['product' => $product, 'incomeLine' => $incomeLine, 'chip' => 'On your card'])
        </a>
    @else
        <a class="lock-hit" href="{{ route('locks.pay', $product) }}">
            @include('partials.lock-card-face', ['product' => $product, 'incomeLine' => $incomeLine, 'chip' => $product->isVip() ? 'VIP' : 'Pay to own', 'chipSolid' => true])
        </a>
    @endif
</article>
