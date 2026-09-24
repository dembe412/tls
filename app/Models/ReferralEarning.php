<?php

namespace App\Models;

use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReferralEarning extends Model
{
    protected $fillable = [
        'user_id',
        'member_id',
        'purchase_id',
        'level',
        'rate_percent',
        'amount',
    ];

    /** The member who earned the commission. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** The member further down the team whose purchase paid it. */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function amountLabel(): string
    {
        return Money::ugx((int) $this->amount);
    }

    public function levelLabel(): string
    {
        return 'Level '.$this->level;
    }
}
