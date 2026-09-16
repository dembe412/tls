<?php

namespace App\Support;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Support\Collection;

class CommunityFeed
{
    public const REFRESH_SECONDS = 30;

    /**
     * @return array{
     *     title: string,
     *     message: string,
     *     member: ?array{name: string, initials: string, avatar: ?string, amount: string, lock: string},
     *     earner_count: int,
     *     refresh_seconds: int
     * }
     */
    public function snapshot(): array
    {
        $members = $this->earningMembers();
        $total = $members->count();
        $tick = intdiv(now()->timestamp, self::REFRESH_SECONDS);
        $featured = $total > 0 ? $members[$tick % $total] : null;

        return [
            'title' => 'Our Community',
            'message' => $this->message($tick, $featured, $total),
            'member' => $featured ? $this->present($featured) : null,
            'earner_count' => $total,
            'refresh_seconds' => self::REFRESH_SECONDS,
        ];
    }

    /**
     * @return Collection<int, Purchase>
     */
    private function earningMembers(): Collection
    {
        return Purchase::query()
            ->with(['user', 'product'])
            ->where('status', 'active')
            ->whereNotNull('activated_at')
            ->where('activated_at', '<=', now()->subDay())
            ->get()
            ->filter(fn (Purchase $purchase) => $purchase->hasCollectedDailyInterest() && $purchase->user && ! $purchase->user->isAdmin())
            ->groupBy('user_id')
            ->map(function (Collection $group): Purchase {
                return $group->sortByDesc(fn (Purchase $purchase) => $purchase->lastEarnedAt()?->timestamp ?? 0)->first();
            })
            ->sortByDesc(fn (Purchase $purchase) => $purchase->lastEarnedAt()?->timestamp ?? 0)
            ->values();
    }

    private function message(int $tick, ?Purchase $featured, int $total): string
    {
        if ($total === 0 || ! $featured) {
            return 'Be the first face on this wall. Own a lock today — 24 hours later, daily interest starts landing.';
        }

        $name = $this->displayName($featured->user);
        $amount = Money::ugx($featured->daily_income);
        $lock = $featured->product?->name ?? 'a TSL lock';

        $lines = [
            "{$name} just collected {$amount} from {$lock}. Every 24 hours, TSL pays members like this.",
            "{$total} members have already earned. The next daily drop could have your photo on it.",
            "Real locks. Real people. {$name} earned {$amount} while the clock ran.",
            'Interest does not sleep. Join the members who get paid every 24 hours.',
            "{$name}'s lock paid {$amount}. Start with a TS-20 and take your seat in this community.",
        ];

        return $lines[$tick % count($lines)];
    }

    /**
     * @return array{name: string, initials: string, avatar: ?string, amount: string, lock: string}
     */
    private function present(Purchase $purchase): array
    {
        $user = $purchase->user;

        return [
            'name' => $this->displayName($user),
            'initials' => $user->initials(),
            'avatar' => $user->avatarUrl(),
            'amount' => Money::ugx($purchase->daily_income),
            'lock' => $purchase->product?->name ?? 'TSL lock',
        ];
    }

    private function displayName(?User $user): string
    {
        if (! $user) {
            return 'A TSL member';
        }

        $parts = preg_split('/\s+/', $user->profileName()) ?: [];
        $first = trim((string) ($parts[0] ?? ''));

        return $first !== '' ? $first : 'A TSL member';
    }
}
