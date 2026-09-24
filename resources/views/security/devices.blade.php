@extends('layouts.app')

@section('title', 'Registered devices — TSL')

@section('content')
<header class="page-head compact">
    <p class="kicker">Security</p>
    <h1>Registered devices</h1>
    <p class="sub">Revoke a browser to stop login and withdrawal approvals from it. Saved sign-ins never skip withdrawal approval.</p>
    <div class="head-actions">
        <a class="text-link" href="{{ route('admin.index') }}">Manager console</a>
    </div>
</header>

<section class="clay-sheet">
    <div class="sheet-title">
        <h2>Add this browser</h2>
    </div>
    <form method="POST" action="{{ route('security.devices.store') }}" class="auth-form">
        @csrf
        <label>
            <span>Device name</span>
            <input class="field-pill" name="name" value="{{ old('name', 'This browser') }}" required maxlength="80">
        </label>
        <p class="hint">Requests waiting for approval are listed in the manager console.</p>
        <button class="btn btn-primary" type="submit">Register this browser</button>
    </form>
</section>

<section class="clay-sheet">
    <h2>Browsers</h2>
    @forelse ($devices as $device)
        <article class="manage-row card-row">
            <div>
                <strong>{{ $device->name }}</strong>
                <p>
                    {{ $device->status }}
                    · registered {{ $device->registered_at?->toFormattedDateString() }}
                    @if ($device->last_used_at)
                        · last used {{ $device->last_used_at->diffForHumans() }}
                    @endif
                    @if ($current && $current->id === $device->id)
                        · this browser
                    @endif
                </p>
            </div>
            @if ($device->isActive())
                <form method="POST" action="{{ route('security.devices.destroy', $device) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-ghost" type="submit">Revoke</button>
                </form>
            @endif
        </article>
    @empty
        <p class="sheet-lead">No browsers registered yet.</p>
    @endforelse
</section>

<section class="clay-sheet">
    <h2>Saved sign-ins</h2>
    @forelse ($sessions as $session)
        <article class="manage-row card-row">
            <div>
                <strong>{{ $session->device?->name ?? 'Browser session' }}</strong>
                <p>
                    {{ $session->revoked_at ? 'revoked' : ($session->isUsable() ? 'active' : 'expired') }}
                    · expires {{ $session->expires_at?->toFormattedDateString() }}
                </p>
            </div>
            @if ($session->isUsable())
                <form method="POST" action="{{ route('security.sessions.destroy', $session) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-ghost" type="submit">Revoke</button>
                </form>
            @endif
        </article>
    @empty
        <p class="sheet-lead">No saved sign-ins.</p>
    @endforelse
</section>
@endsection
