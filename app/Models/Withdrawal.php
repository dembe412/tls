<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Withdrawal extends Model
{
    protected $fillable = [
        'user_id',
        'purchase_id',
        'amount',
        'fee_amount',
        'status',
        'reference',
        'requested_at',
        'authorized_at',
        'expires_at',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'authorized_at' => 'datetime',
            'expires_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(AuthChallenge::class);
    }

    public function isAuthorized(): bool
    {
        return in_array($this->status, ['authorized', 'pending', 'paid'], true) || $this->authorized_at !== null;
    }

    public static function feePercent(): int
    {
        return max(0, (int) config('payments.withdraw_fee_percent', 6));
    }

    public static function feeFor(int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        return (int) round($amount * self::feePercent() / 100);
    }

    public function netAmount(): int
    {
        return max(0, (int) $this->amount - (int) $this->fee_amount);
    }
}
