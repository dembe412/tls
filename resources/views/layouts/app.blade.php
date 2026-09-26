<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'TSL Smart Locks')</title>
    <meta name="description" content="@yield('description', 'Browse TSL Tuya smart locks, earn a daily amount and cash out after 35 days.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="icon" href="{{ asset('images/logo.jpg') }}" type="image/jpeg">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v=40">
</head>
<body>
    <div class="app-shell">
        <header class="site-bar">
            <a class="brand-logo" href="{{ route('home') }}" aria-label="TSL home">
                <img
                    src="{{ asset('images/logo.jpg') }}"
                    alt="TSL"
                    width="228"
                    height="224"
                    decoding="async"
                    fetchpriority="high"
                >
            </a>
            @include('partials.header-action')
        </header>
        @if (session('success') || session('info') || $errors->any())
            <div class="toast-stack" role="status">
                @if (session('success'))
                    <p class="toast toast-ok">{{ session('success') }}</p>
                @endif
                @if (session('info'))
                    <p class="toast toast-info">{{ session('info') }}</p>
                @endif
                @if ($errors->any())
                    <p class="toast toast-err">{{ $errors->first() }}</p>
                @endif
            </div>
        @endif

        <main class="page">
            @yield('content')
        </main>

        @include('partials.bottom-nav')
        @include('partials.signup-modal')
        @include('partials.profile-modal')
    </div>
    <script src="{{ asset('js/app.js') }}?v=17"></script>
</body>
</html>
