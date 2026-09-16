<?php

namespace App\Models;

use Database\Factories\BonusRedemptionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusRedemption extends Model
{
    /** @use HasFactory<BonusRedemptionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'applied' => 'Applied',
            'rejected' => 'Rejected',
            default => 'Waiting for TSL',
        };
    }
}
