<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\StaffDevice;
use App\Models\User;
use App\Support\Security\DeviceGuard;
use App\Support\Security\PersistentSessions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    public function __construct(
        private DeviceGuard $devices,
        private PersistentSessions $persistent,
    ) {}

    public function enroll(Request $request)
    {
        $user = $this->enrollmentUser($request);

        if (! $user) {
            return redirect()->route('login')->with('info', 'Sign in first, then register this browser.');
        }

        return view('security.enroll', [
            'user' => $user,
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);

        return view('security.devices', [
            'user' => $user,
            'devices' => $user->staffDevices()->latest('last_used_at')->get(),
            'sessions' => $user->persistentLogins()->with('device')->latest()->get(),
            'current' => $this->devices->current($request),
        ]);
    }

    public function store(Request $request)
    {
        $enrolling = $this->enrollmentUser($request);
        $user = $request->user()?->isAdmin() ? $request->user() : $enrolling;
        abort_unless($user?->isAdmin(), 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
        ]);

        $device = $this->devices->register($user, $request, $data['name']);

        if ($enrolling && ! Auth::check()) {
            Auth::login($user, false);
            $request->session()->regenerate();
            $request->session()->forget(['staff_enrollment_user_id', 'staff_enrollment_expires', 'pending_login']);

            if ($request->session()->pull('keep_signed_in')) {
                $this->persistent->issue($user, $request, $device);
            }
        }

        $home = route('security.devices');

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'This browser is now a registered approval device.',
                'redirect' => $home,
            ]);
        }

        return redirect($home)->with('success', 'This browser is now a registered approval device.');
    }

    public function destroy(Request $request, StaffDevice $device)
    {
        $user = $request->user();
        abort_unless($user?->isAdmin() && $device->user_id === $user->id, 403);

        $this->devices->revoke($device, $request);

        return back()->with('success', $device->name.' was revoked. It can no longer approve logins or withdrawals.');
    }

    public function revokeSession(Request $request, int $session)
    {
        $user = $request->user();
        abort_unless($user?->isAdmin(), 403);

        $login = $user->persistentLogins()->whereKey($session)->firstOrFail();
        $this->persistent->revoke($login, $request);

        return back()->with('success', 'That saved sign-in was revoked.');
    }

    private function enrollmentUser(Request $request): ?User
    {
        $id = $request->session()->get('staff_enrollment_user_id');
        $expires = (int) $request->session()->get('staff_enrollment_expires', 0);

        if (! $id || $expires < now()->timestamp) {
            $request->session()->forget(['staff_enrollment_user_id', 'staff_enrollment_expires']);

            return null;
        }

        $user = User::query()->find($id);

        return $user?->isAdmin() ? $user : null;
    }
}
