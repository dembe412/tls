<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HelpController extends Controller
{
    public function faq()
    {
        return view('help.faq', [
            'questions' => $this->questions(),
        ]);
    }

    public function bonus(Request $request)
    {
        $user = $request->user();

        return view('help.bonus', [
            'redemptions' => $user
                ? $user->bonusRedemptions()->latest()->get()
                : collect(),
        ]);
    }

    public function redeem(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $request->merge([
            'code' => strtoupper((string) preg_replace('/\s+/', '', (string) $request->input('code'))),
        ]);

        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'min:4',
                'max:24',
                'regex:/^[A-Z0-9-]+$/',
                Rule::unique('bonus_redemptions', 'code')->where('user_id', $user->id),
            ],
        ], [
            'code.unique' => 'You already sent this bonus code.',
            'code.regex' => 'Use letters, numbers or dashes only.',
        ]);

        $user->bonusRedemptions()->create([
            'code' => $data['code'],
            'status' => 'pending',
        ]);

        return back()->with('success', 'Bonus code sent. TSL will apply it after a check.');
    }

    /**
     * @return list<array{q: string, a: string}>
     */
    private function questions(): array
    {
        return [
            [
                'q' => 'How do I buy a lock?',
                'a' => 'Tap a lock on Home or Products, send the exact price by Airtel Money or MTN, then enter the transaction ID. The TSL manager switches the lock on after matching your payment.',
            ],
            [
                'q' => 'How does daily interest work?',
                'a' => 'Each lock pays the daily amount shown on its card. Earnings start the day the manager marks it as bought, and you can cash out when the cycle ends — 35 days for locks.',
            ],
            [
                'q' => 'What are VIP levels?',
                'a' => 'VIP products sit on Products and My account. They follow a monthly salary paid on the 1st, with a recharge and ABC member requirement on each gold card.',
            ],
            [
                'q' => 'How do I pay?',
                'a' => 'Open the lock, choose Airtel Money or MTN, send the exact amount to the number shown, then type the transaction ID. Your name and phone are attached automatically.',
            ],
            [
                'q' => 'Can I sign in with a username or a phone?',
                'a' => 'Yes. Create an account with a username, a phone, or both. Use either one plus your password to sign in. Update them from your profile photo in the top right.',
            ],
            [
                'q' => 'How do I redeem a bonus?',
                'a' => 'Tap Redeem bonus on Home, sign in, and enter the code TSL gave you. The manager checks it and applies it to your account.',
            ],
        ];
    }
}
