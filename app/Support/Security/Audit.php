<?php

namespace App\Support\Security;

use App\Models\SecurityLog;
use App\Models\StaffDevice;
use App\Models\User;
use Illuminate\Http\Request;

class Audit
{
    public static function record(string $event, Request $request, ?User $user = null, ?StaffDevice $device = null, array $context = []): void
    {
        SecurityLog::query()->create([
            'user_id' => $user?->id,
            'device_id' => $device?->id,
            'event' => $event,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'context' => $context ?: null,
        ]);
    }
}
