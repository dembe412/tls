@php
    $person = $person ?? null;
    $size = $size ?? 'md';
@endphp
@if ($person?->avatarUrl())
    <img class="avatar avatar-{{ $size }}" src="{{ $person->avatarUrl() }}" alt="{{ $person->profileName() }}">
@else
    <span class="avatar avatar-{{ $size }} avatar-fallback" aria-hidden="true">{{ $person?->initials() ?? 'TS' }}</span>
@endif
