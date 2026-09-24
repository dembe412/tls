<?php

namespace App\Support\Security;

use App\Models\AuthChallenge;
use App\Models\User;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChallengeVault
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function issue(string $type, Request $request, ?User $user = null, ?Withdrawal $withdrawal = null, array $context = []): AuthChallenge
    {
        $ttl = (int) config('security.challenge_ttl', 120);

        return AuthChallenge::query()->create([
            'public_id' => (string) Str::uuid(),
            'type' => $type,
            'user_id' => $user?->id,
            'withdrawal_id' => $withdrawal?->id,
            'token_hash' => hash('sha256', random_bytes(32)),
            'status' => 'pending',
            'context' => $context,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 512),
            'expires_at' => now()->addSeconds($ttl),
        ]);
    }

    /**
     * Requests still waiting for a manager decision, newest first.
     *
     * @return Collection<int, AuthChallenge>
     */
    public function pending()
    {
        return AuthChallenge::query()
            ->with(['user', 'withdrawal'])
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->latest()
            ->get();
    }
}
