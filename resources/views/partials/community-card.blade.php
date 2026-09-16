@php
    $community = $community ?? ['title' => 'Our Community', 'message' => '', 'member' => null, 'earner_count' => 0, 'refresh_seconds' => 30];
    $member = $community['member'] ?? null;
    $earnerCount = (int) ($community['earner_count'] ?? 0);
@endphp
<aside
    class="community-card"
    data-community-card
    data-feed-url="{{ route('community.feed') }}"
    data-refresh-seconds="{{ $community['refresh_seconds'] ?? 30 }}"
    aria-live="polite"
>
    <div class="community-portrait" data-community-portrait>
        @if (! empty($member['avatar']))
            <img src="{{ $member['avatar'] }}" alt="{{ $member['name'] }}">
        @else
            <span class="community-portrait-fallback" aria-hidden="true">{{ $member['initials'] ?? '+' }}</span>
        @endif
    </div>
    <div class="community-copy">
        <p class="community-live"><i></i> Live</p>
        <h2>{{ $community['title'] ?? 'Our Community' }}</h2>
        <p class="community-member-name" data-community-name>{{ $member['name'] ?? 'Your photo here' }}</p>
        <p class="community-amount" data-community-amount>
            @if ($member)
                {{ $member['amount'] }} from {{ $member['lock'] }}
            @endif
        </p>
        <p class="community-message" data-community-message>{{ $community['message'] }}</p>
        <p class="community-meta" data-community-meta>
            @if ($earnerCount > 0)
                {{ $earnerCount }} {{ $earnerCount === 1 ? 'member is' : 'members are' }} collecting daily interest
            @else
                Waiting for the first 24-hour payout
            @endif
        </p>
    </div>
</aside>
