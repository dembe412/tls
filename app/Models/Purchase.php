<?php

namespace App\Models;

use App\Support\Money;
use App\Support\PaymentMethods;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Purchase extends Model
{
    protected $fillable = [
        'user_id',
        'product_id',
        'status',
        'payment_method',
        'payment_number',
        'transaction_id',
        'payer_name',
        'principal',
        'daily_income',
        'duration_days',
        'activated_at',
        'matures_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'matures_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function daysRun(): int
    {
        if (! $this->activated_at) {
            return 0;
        }

        $days = (int) $this->activated_at->diffInDays(now());

        return max(0, min($days, $this->duration_days));
    }

    public function lastEarnedAt(): ?Carbon
    {
        $days = $this->daysRun();

        if ($days < 1 || $this->activated_at === null) {
            return null;
        }

        return $this->activated_at->copy()->addDays($days);
    }

    public function hasCollectedDailyInterest(): bool
    {
        return $this->status === 'active' && $this->lastEarnedAt() !== null;
    }

    public function daysLeft(): int
    {
        if (! $this->activated_at) {
            return $this->duration_days;
        }

        return max(0, $this->duration_days - $this->daysRun());
    }

    public function earnedSoFar(): int
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return $this->daysRun() * (int) $this->daily_income;
    }

    public function withdrawnAmount(): int
    {
        return (int) $this->withdrawals
            ->where('status', '!=', 'rejected')
            ->sum('amount');
    }

    public function availableToCashOut(): int
    {
        return max(0, $this->earnedSoFar() - $this->withdrawnAmount());
    }

    public function isMatured(): bool
    {
        return $this->status === 'active' && $this->daysLeft() === 0;
    }

    public function principalLabel(): string
    {
        return Money::ugx($this->principal);
    }

    public function paymentMethodLabel(): string
    {
        return PaymentMethods::find((string) $this->payment_method)['name'] ?? 'Mobile money';
    }
}
