<section class="catalog vip-board">
    <div class="vip-head">
        <p class="vip-brand">TSL</p>
        <p class="vip-banner">Marketing benefits</p>
    </div>
    <div class="vip-list">
        @foreach ($vips as $vip)
            @include('partials.vip-row', ['vip' => $vip])
        @endforeach
    </div>
    <p class="vip-foot">Monthly salary is paid on the 1st of each month. Tap a VIP to buy it.</p>
    <p class="vip-mark">Tuya smart lock</p>
</section>
