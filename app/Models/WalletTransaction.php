<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'purchase_id',
        'wallet',
        'type',
        'amount',
        'balance_after',
        'reference',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function isCredit(): bool
    {
        return $this->amount >= 0;
    }

    public function amountLabel(): string
    {
        return ($this->isCredit() ? '+' : '-').Money::ugx(abs((int) $this->amount));
    }

    public function walletLabel(): string
    {
        return $this->wallet === 'recharge' ? 'Recharge balance' : 'Account balance';
    }
}
