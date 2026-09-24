<?php

namespace App\Http\Controllers;

use App\Models\BonusCode;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('bonus.index', [
            'claimed' => $user
                ? $user->claimedBonuses()->latest('claimed_at')->get()
                : collect(),
            'bonusMinutes' => (int) round(((int) config('support.bonus_code_ttl')) / 60),
        ]);
    }

    /**
     * Landing page for a link a manager shared.
     */
    public function show(Request $request, string $code)
    {
        $bonus = BonusCode::query()->where('code', strtoupper($code))->first();

        if (! $bonus) {
            return redirect()->route('bonus')->with('info', 'That bonus link is not a TSL code.');
        }

        $user = $request->user();

        if (! $user) {
            $request->session()->put('url.intended', $bonus->shareUrl());
        }

        return view('bonus.claim', [
            'bonus' => $bonus,
            'user' => $user,
        ]);
    }

    public function claim(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $request->merge([
            'code' => strtoupper((string) preg_replace('/\s+/', '', (string) $request->input('code'))),
        ]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:32'],
        ]);

        $bonus = BonusCode::query()->where('code', $data['code'])->first();

        if (! $bonus) {
            throw ValidationException::withMessages(['code' => 'That bonus code does not exist.']);
        }

        if ($bonus->isClaimed()) {
            throw ValidationException::withMessages(['code' => 'That bonus code has already been claimed.']);
        }

        if ($bonus->hasExpired()) {
            throw ValidationException::withMessages(['code' => 'That bonus code expired. Ask the manager to send a new one.']);
        }

        if (! $bonus->isFor($user)) {
            throw ValidationException::withMessages(['code' => 'That bonus code was sent to another member.']);
        }

        $won = BonusCode::query()
            ->whereKey($bonus->id)
            ->whereNull('claimed_at')
            ->where('expires_at', '>', now())
            ->update([
                'claimed_at' => now(),
                'claimed_by_id' => $user->id,
            ]);

        if ($won === 0) {
            throw ValidationException::withMessages(['code' => 'That bonus code was just claimed. Ask the manager to send a new one.']);
        }

        Wallet::credit(
            $user,
            Wallet::ACCOUNT,
            (int) $bonus->amount,
            'bonus',
            'Bonus code '.$bonus->code,
            $bonus->code,
        );

        return redirect()->route('account')->with(
            'success',
            $bonus->amountLabel().' bonus added to your account balance.'
        );
    }
}
