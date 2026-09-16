@php
    $level = max(1, min(6, (int) ($vip->sort_order ?: 1)));
    $starCount = $level + 2;
    $seed = abs(crc32((string) $vip->id.$vip->name));
@endphp
<span class="vip-stars" aria-hidden="true">
    @for ($i = 0; $i < $starCount; $i++)
        @php
            $top = 8 + (($seed >> ($i * 3)) % 72);
            $left = 4 + (($seed >> ($i * 5)) % 84);
            $size = 8 + ($i % 5) + (int) floor($level / 2);
            $delay = number_format($i * 0.22, 2, '.', '');
        @endphp
        <svg class="vip-star" style="width: {{ $size }}px; height: {{ $size }}px; top: {{ $top }}%; left: {{ $left }}%; animation-delay: {{ $delay }}s" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 0l2.9 8.3L24 9.3l-6.6 5.9L19.4 24 12 19.1 4.6 24l2-8.8L0 9.3l9.1-1z"/>
        </svg>
    @endfor
</span>
<div class="vip-card-body">
    <div class="vip-card-top">
        <span class="vip-icon">
            <img src="{{ $vip->imageUrl() }}" alt="">
        </span>
        <strong class="vip-badge">{{ $vip->name }}</strong>
    </div>
    <p>{{ $vip->vipRequirement() }}</p>
    <b>{{ $vip->salaryLabel() }}</b>
</div>
