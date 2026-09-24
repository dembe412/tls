<?php

namespace App\Support\Security;

use App\Models\PersistentLogin;
use App\Models\StaffDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class PersistentSessions
{
    public function issue(User $user, Request $request, ?StaffDevice $device = null): void
    {
        $selector = Str::random(24);
        $validator = Str::password(48, symbols: false);
        $days = (int) config('security.persistent_days', 30);

        PersistentLogin::query()->create([
            'user_id' => $user->id,
            'device_id' => $device?->id,
            'selector' => $selector,
            'token_hash' => hash_hmac('sha256', $validator, (string) config('app.key')),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'ip_address' => $request->ip(),
            'expires_at' => now()->addDays($days),
            'last_used_at' => now(),
        ]);

        Cookie::queue(cookie(
            config('security.persist_cookie'),
            $selector.'.'.$validator,
            $days * 24 * 60,
            '/',
            null,
            app()->environment('production') || (bool) config('session.secure'),
            true,
            false,
            'lax'
        ));

        Audit::record('persist_issued', $request, $user, $device);
    }

    public function authenticate(Request $request): ?User
    {
        $raw = (string) $request->cookie(config('security.persist_cookie'));
        [$selector, $validator] = array_pad(explode('.', $raw, 2), 2, '');

        if ($selector === '' || $validator === '') {
            return null;
        }

        $row = PersistentLogin::query()
            ->where('selector', $selector)
            ->whereNull('revoked_at')
            ->first();

        if (! $row || ! $row->isUsable() || ! hash_equals($row->token_hash, hash_hmac('sha256', $validator, (string) config('app.key')))) {
            return null;
        }

        if ($row->device && ! $row->device->isActive()) {
            return null;
        }

        $row->forceFill(['last_used_at' => now()])->save();
        $row->device?->forceFill(['last_used_at' => now()])->save();

        return $row->user;
    }

    public function forget(Request $request): void
    {
        $raw = (string) $request->cookie(config('security.persist_cookie'));
        [$selector] = array_pad(explode('.', $raw, 2), 2, '');

        if ($selector !== '') {
            PersistentLogin::query()->where('selector', $selector)->update(['revoked_at' => now()]);
        }

        Cookie::queue(Cookie::forget(config('security.persist_cookie')));
    }

    public function revoke(PersistentLogin $login, Request $request): void
    {
        $login->update(['revoked_at' => now()]);
        Audit::record('persist_revoked', $request, $login->user, $login->device);
    }
}
