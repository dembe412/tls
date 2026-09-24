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
}
