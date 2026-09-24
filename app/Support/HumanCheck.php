<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Self-hosted "prove you are not a robot" check for public forms.
 *
 * The browser must tick the box, which runs a small script that turns the
 * seed on the page into the token the server expects. A script that posts
 * the form directly never produces the token, leaves the honeypot filled,
 * or arrives faster than a person can read the form.
 */
class HumanCheck
{
    private const SESSION_KEY = 'human_check';

    private const TRAP_FIELD = 'website';

    private const MIN_SECONDS = 2;

    public static function issue(Request $request): string
    {
        $seed = strtoupper(Str::random(12));

        $request->session()->put(self::SESSION_KEY, [
            'seed' => $seed,
            'issued_at' => now()->timestamp,
        ]);

        return $seed;
    }

    public static function answerFor(string $seed): string
    {
        return strrev($seed);
    }

    /**
     * @throws ValidationException
     */
    public static function validate(Request $request): void
    {
        $state = (array) $request->session()->get(self::SESSION_KEY, []);
        $seed = (string) ($state['seed'] ?? '');
        $issuedAt = (int) ($state['issued_at'] ?? 0);

        $token = trim((string) $request->input('human_token'));
        $trap = trim((string) $request->input(self::TRAP_FIELD));

        $request->session()->forget(self::SESSION_KEY);

        $passed = $seed !== ''
            && $token !== ''
            && $trap === ''
            && hash_equals(self::answerFor($seed), $token)
            && (now()->timestamp - $issuedAt) >= self::MIN_SECONDS;

        if (! $passed) {
            throw ValidationException::withMessages([
                'human_token' => 'Tick "I am not a robot" to confirm you are a person, then try again.',
            ]);
        }
    }
}
