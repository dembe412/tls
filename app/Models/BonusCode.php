<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BonusCode extends Model
{
    protected $fillable = [
        'code',
        'amount',
        'created_by_id',
        'assigned_user_id',
        'claimed_by_id',
        'note',
        'expires_at',
        'claimed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'claimed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /** The member this code was made for, when it is not open to anyone. */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function claimedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'claimed_by_id');
    }

    public static function newCode(): string
    {
        do {
            $code = 'TSL'.strtoupper(Str::random(7));
        } while (static::query()->where('code', $code)->exists());

        return $code;
    }

    public function isClaimed(): bool
    {
        return $this->claimed_at !== null;
    }

    public function hasExpired(): bool
    {
        return ! $this->isClaimed() && $this->expires_at->isPast();
    }

    public function isOpen(): bool
    {
        return ! $this->isClaimed() && ! $this->hasExpired();
    }

    public function isFor(User $user): bool
    {
        return $this->assigned_user_id === null || $this->assigned_user_id === $user->id;
    }

    public function shareUrl(): string
    {
        return route('bonus.claim', $this);
    }

    public function amountLabel(): string
    {
        return Money::ugx((int) $this->amount);
    }

    public function statusLabel(): string
    {
        return match (true) {
            $this->isClaimed() => 'Claimed',
            $this->hasExpired() => 'Expired',
            default => 'Waiting to be claimed',
        };
    }
}
