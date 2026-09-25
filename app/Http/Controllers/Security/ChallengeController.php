<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use App\Models\AuthChallenge;
use App\Support\Security\Audit;
use App\Support\Security\DeviceGuard;
use App\Support\Security\StaffAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ChallengeController extends Controller
{
    public function __construct(
        private DeviceGuard $devices,
        private StaffAuthenticator $authenticator,
    ) {}

    public function wait(Request $request, AuthChallenge $challenge)
    {
        $pending = $request->session()->get('pending_login');
        abort_unless(($pending['challenge'] ?? null) === $challenge->public_id, 403);
        abort_unless($challenge->type === 'login', 404);

        $challenge->expireIfNeeded();

        return view('security.wait', [
            'challenge' => $challenge->fresh(),
            'device' => $this->devices->current($request),
        ]);
    }

    public function status(Request $request, AuthChallenge $challenge)
    {
        $pending = $request->session()->get('pending_login');
        abort_unless(($pending['challenge'] ?? null) === $challenge->public_id, 403);

        $challenge->expireIfNeeded();
        $challenge->refresh();

        return response()->json([
            'status' => $challenge->status,
            'redirect' => $challenge->status === 'approved'
                ? route('security.challenge.complete', $challenge)
                : null,
        ]);
    }

    public function complete(Request $request, AuthChallenge $challenge)
    {
        $pending = $request->session()->get('pending_login');
        if (($pending['challenge'] ?? null) === $challenge->public_id) {
            try {
                $this->authenticator->complete($challenge, $request);
            } catch (\Throwable) {
                // Already completed or expired
            }
        }

        if ($request->user()?->isAdmin()) {
            return redirect()
                ->intended(route('admin.index'))
                ->with('success', 'Welcome back.');
        }

        return redirect()->route('login');
    }

    public function review(Request $request, AuthChallenge $challenge)
    {
        $device = $this->devices->current($request);
        abort_unless($device, 403, 'This browser is not a registered approval device.');

        $challenge->expireIfNeeded();
        $challenge->refresh();
        $this->assertDeviceMaySee($challenge, $device);

        return view('security.review', [
            'challenge' => $challenge,
            'device' => $device,
            'amount' => $challenge->context['amount_label'] ?? null,
            'reference' => $challenge->context['reference'] ?? null,
        ]);
    }

    public function decide(Request $request, AuthChallenge $challenge)
    {
        $this->throttle($request, 'challenge-decide:'.$request->ip());

        $device = $this->devices->current($request);
        abort_unless($device, 403, 'This browser is not a registered approval device.');

        $data = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
        ]);

        $challenge->expireIfNeeded();
        $challenge->refresh();

        if (! $challenge->isPending()) {
            throw ValidationException::withMessages([
                'decision' => 'That request has expired or was already used.',
            ]);
        }

        $this->assertDeviceMaySee($challenge, $device);
        $this->devices->touch($device);

        $challenge->update([
            'status' => $data['decision'],
            'approved_device_id' => $device->id,
            'resolved_at' => now(),
        ]);

        if ($challenge->type === 'withdrawal' && $challenge->withdrawal) {
            $withdrawal = $challenge->withdrawal;

            if ($data['decision'] === 'approved') {
                $withdrawal->update([
                    'status' => 'authorized',
                    'authorized_at' => now(),
                ]);
            } else {
                $withdrawal->update(['status' => 'rejected']);
            }
        }

        Audit::record(
            $challenge->type.'_'.$data['decision'],
            $request,
            $challenge->user ?: $device->user,
            $device,
            [
                'challenge' => $challenge->public_id,
                'withdrawal_id' => $challenge->withdrawal_id,
                'amount' => $challenge->context['amount_label'] ?? null,
            ]
        );

        $message = $data['decision'] === 'approved'
            ? 'Request approved.'
            : 'Request rejected.';

        if ($challenge->type === 'login' && $data['decision'] === 'approved') {
            $pending = $request->session()->get('pending_login');
            if (($pending['challenge'] ?? null) === $challenge->public_id) {
                return redirect()->route('security.challenge.complete', $challenge);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'status' => $data['decision'], 'message' => $message]);
        }

        return redirect()->route('security.review', $challenge)->with('success', $message);
    }

    private function assertDeviceMaySee(AuthChallenge $challenge, $device): void
    {
        if ($challenge->type === 'login') {
            abort_unless((int) $device->user_id === (int) $challenge->user_id, 403);
        } else {
            abort_unless($device->user?->isAdmin(), 403);
        }
    }

    private function throttle(Request $request, string $key): void
    {
        if (RateLimiter::tooManyAttempts($key, 20)) {
            abort(429, 'Too many attempts. Wait a moment.');
        }

        RateLimiter::hit($key, 60);
    }
}
