<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffDevice extends Model
{
    protected $fillable = [
        'user_id',
        'device_uuid',
        'name',
        'secret_hash',
        'user_agent',
        'ip_address',
        'status',
        'registered_at',
        'last_used_at',
        'revoked_at',
    ];

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'last_used_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function persistentLogins(): HasMany
    {
        return $this->hasMany(PersistentLogin::class, 'device_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->revoked_at === null;
    }
}
