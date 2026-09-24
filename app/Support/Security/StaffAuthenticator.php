<?php

namespace App\Support\Security;

use App\Models\AuthChallenge;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StaffAuthenticator
{
    public function __construct(
        private ChallengeVault $challenges,
        private DeviceGuard $devices,
        private PersistentSessions $persistent,
    ) {}

    public function complete(AuthChallenge $challenge, Request $request): User
    {
        $challenge->expireIfNeeded();
        $challenge->refresh();

        if ($challenge->type !== 'login' || $challenge->status !== 'approved' || $challenge->consumed_at) {
            throw ValidationException::withMessages([
                'login' => 'That login request is no longer valid.',
            ]);
        }

        $user = $challenge->user;
        abort_unless($user && $user->isAdmin(), 403);

        $challenge->update([
            'status' => 'consumed',
            'consumed_at' => now(),
        ]);

        Auth::login($user, false);
        $request->session()->regenerate();
        $request->session()->forget(['staff_enrollment_user_id', 'pending_login']);

        $device = $this->devices->current($request);
        if ($request->session()->pull('keep_signed_in')) {
            $this->persistent->issue($user, $request, $device);
        }

        Audit::record('login_completed', $request, $user, $device);

        return $user;
    }

    public function loginStaff(User $user, Request $request, bool $keepSignedIn): mixed
    {
        $device = $this->devices->current($request);
        $hasDevices = $user->staffDevices()->where('status', 'active')->whereNull('revoked_at')->exists();

        if (! $hasDevices) {
            $request->session()->put('staff_enrollment_user_id', $user->id);
            $request->session()->put('keep_signed_in', $keepSignedIn);
            $request->session()->put('staff_enrollment_expires', now()->addSeconds((int) config('security.enrollment_ttl'))->timestamp);

            Audit::record('device_enrollment_started', $request, $user);

            return redirect()->route('security.devices.enroll')
                ->with('info', 'Register this browser to receive login and withdrawal approvals.');
        }

        $challenge = $this->challenges->issue('login', $request, $user, null, [
            'summary' => trim($request->ip().' · '.substr((string) $request->userAgent(), 0, 80)),
            'keep_signed_in' => $keepSignedIn,
        ]);

        $request->session()->put('pending_login', [
            'user_id' => $user->id,
            'challenge' => $challenge->public_id,
            'keep_signed_in' => $keepSignedIn,
        ]);
        $request->session()->put('keep_signed_in', $keepSignedIn);

        Audit::record('login_challenged', $request, $user, $device, [
            'challenge' => $challenge->public_id,
        ]);

        return redirect()->route('security.challenge.wait', $challenge);
    }
}
