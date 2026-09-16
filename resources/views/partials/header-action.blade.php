<div class="header-action">
    @auth
        <button type="button" class="header-profile" data-open-profile aria-label="Open profile">
            @include('partials.avatar', ['person' => auth()->user(), 'size' => 'header'])
        </button>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-signout" type="submit">Sign out</button>
        </form>
    @else
        @unless (request()->routeIs('login', 'register'))
            <a class="btn-signout" href="{{ route('login') }}">Sign in</a>
        @endunless
    @endauth
</div>
