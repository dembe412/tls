@php
    $tab = request()->routeIs('home') ? 'home'
        : (request()->routeIs('products') ? 'products'
        : (request()->routeIs('news', 'news.show') ? 'news'
        : (request()->routeIs('account', 'login', 'register', 'admin.index') ? 'account' : '')));
@endphp
<nav class="bottom-nav" aria-label="Main">
    <a href="{{ route('home') }}" class="{{ $tab === 'home' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 10.5 12 4l8 6.5V20a1 1 0 0 1-1 1h-5v-6H10v6H5a1 1 0 0 1-1-1v-9.5Z"/></svg>
        Home
    </a>
    <a href="{{ route('products') }}" class="{{ $tab === 'products' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="7" height="7" rx="1.5"/><rect x="14" y="4" width="7" height="7" rx="1.5"/><rect x="3" y="13" width="7" height="7" rx="1.5"/><rect x="14" y="13" width="7" height="7" rx="1.5"/></svg>
        Products
    </a>
    <a href="{{ route('news') }}" class="{{ $tab === 'news' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 5h12a2 2 0 0 1 2 2v12H6a2 2 0 0 1-2-2V5Z"/><path d="M18 7h2a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H8"/><path d="M8 10h6M8 14h4"/></svg>
        News
    </a>
    <a href="{{ route('account') }}" class="{{ $tab === 'account' ? 'is-active' : '' }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="3.5"/><path d="M5 19.5c1.4-3.2 4-5 7-5s5.6 1.8 7 5"/></svg>
        My account
    </a>
</nav>
