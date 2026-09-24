<?php

namespace App\Support\Security;

use App\Models\StaffDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class DeviceGuard
{
    public function current(Request $request): ?StaffDevice
    {
        $raw = (string) $request->cookie(config('security.device_cookie'));
        [$uuid, $secret] = array_pad(explode('.', $raw, 2), 2, '');

        if ($uuid === '' || $secret === '') {
            return null;
        }

        $device = StaffDevice::query()
            ->where('device_uuid', $uuid)
            ->where('status', 'active')
            ->whereNull('revoked_at')
            ->first();

        if (! $device || ! $this->check($secret, $device->secret_hash)) {
            return null;
        }

        return $device;
    }

    public function register(User $user, Request $request, string $name): StaffDevice
    {
        $secret = Str::password(40, symbols: false);
        $device = new StaffDevice([
            'user_id' => $user->id,
            'device_uuid' => (string) Str::uuid(),
            'name' => $name,
            'secret_hash' => $this->digest($secret),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'ip_address' => $request->ip(),
            'status' => 'active',
            'registered_at' => now(),
            'last_used_at' => now(),
        ]);
        $device->save();

        $this->queueCookie($device, $secret);

        Audit::record('device_registered', $request, $user, $device, [
            'name' => $device->name,
        ]);

        return $device;
    }

    public function revoke(StaffDevice $device, Request $request): void
    {
        $device->update([
            'status' => 'revoked',
            'revoked_at' => now(),
        ]);

        $device->persistentLogins()->whereNull('revoked_at')->update(['revoked_at' => now()]);

        Audit::record('device_revoked', $request, $device->user, $device);
    }

    public function touch(StaffDevice $device): void
    {
        $device->forceFill(['last_used_at' => now()])->save();
    }

    public function cookie(StaffDevice $device, string $secret)
    {
        $minutes = 60 * 24 * 400;

        return cookie(
            config('security.device_cookie'),
            $device->device_uuid.'.'.$secret,
            $minutes,
            '/',
            null,
            $this->secure(),
            true,
            false,
            'lax'
        );
    }

    public function digest(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    public function check(string $plain, string $hash): bool
    {
        return hash_equals($hash, $this->digest($plain));
    }

    private function queueCookie(StaffDevice $device, string $secret): void
    {
        Cookie::queue($this->cookie($device, $secret));
    }

    private function secure(): bool
    {
        return app()->environment('production') || (bool) config('session.secure');
    }
}
