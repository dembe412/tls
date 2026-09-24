<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthChallenge extends Model
{
    protected $fillable = [
        'public_id',
        'type',
        'user_id',
        'withdrawal_id',
        'approved_device_id',
        'token_hash',
        'status',
        'context',
        'ip_address',
        'user_agent',
        'expires_at',
        'resolved_at',
        'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'expires_at' => 'datetime',
            'resolved_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawal(): BelongsTo
    {
        return $this->belongsTo(Withdrawal::class);
    }

    public function approvedDevice(): BelongsTo
    {
        return $this->belongsTo(StaffDevice::class, 'approved_device_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture() && $this->consumed_at === null;
    }

    public function expireIfNeeded(): void
    {
        if ($this->status === 'pending' && $this->expires_at->isPast()) {
            $this->update(['status' => 'expired', 'resolved_at' => now()]);

            if ($this->type === 'withdrawal' && $this->withdrawal?->status === 'awaiting_approval') {
                $this->withdrawal->update(['status' => 'expired']);
            }
        }
    }
}
