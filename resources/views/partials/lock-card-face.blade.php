@php
    $isPhoto = filled($product->image_path) && ! str_ends_with(strtolower((string) $product->image_path), '.svg');
@endphp
<div class="lock-visual{{ $isPhoto ? ' is-photo' : ' is-art' }} lock-visual-{{ $product->image_key }}">
    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }} {{ $product->isVip() ? 'VIP' : 'smart lock' }}">
</div>
<div class="lock-copy">
    <h3>{{ $product->name }}</h3>
    <p class="price">{{ $product->priceLabel() }}</p>
    <p class="daily">{{ $incomeLine }}</p>
    <span class="chip{{ ! empty($chipSolid) ? ' chip-solid' : '' }}">{{ $chip }}</span>
</div>
