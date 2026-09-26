<?php

namespace App\Http\Controllers;

class HelpController extends Controller
{
    public function faq()
    {
        return view('help.faq', [
            'questions' => $this->questions(),
        ]);
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
                'a' => 'Each lock pays the daily amount shown on its card. Earnings start the day the manager marks it as bought and continue for up to 35 days. Cash out is separate — you may withdraw any day once available earnings reach 2,000 UGX.',
            ],
            [
                'q' => 'What are VIP levels?',
                'a' => 'Registered members with no top-up are Ordinary. After any recharge you become VIP 0. VIP 1 to VIP 5 unlock on Team when your cumulative recharge, ABC team size and a purchased product meet that gold card.',
            ],
            [
                'q' => 'How do I withdraw?',
                'a' => 'Open My account and tap Cash out on an owner card any day once available earnings are at least 2,000 UGX. There is no wait for day 35. A 6% charge applies on each withdraw — you receive the rest after the fee. A manager then marks the cash-out as paid.',
            ],
            [
                'q' => 'How do managers sign in?',
                'a' => 'Managers enter username/phone and password, then approve the login from a registered browser. There is no SMS code. Requests waiting for a decision are listed in the manager console. Withdrawals always need a fresh device approval, even if you kept this device signed in.',
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
                'a' => 'A manager sends you a bonus link or code. Open it, sign in, and tap Claim. The money lands in your account balance straight away. A bonus code expires a few minutes after it is made, so claim it quickly.',
            ],
            [
                'q' => 'How much do I earn for inviting friends?',
                'a' => 'Share your invite link from Team or My account. When someone you invited buys a lock you earn 5% of the price, and 10% when they buy a VIP. Level B earns 2% and Level C earns 1%. The commission goes into your account balance the moment their product is switched on.',
            ],
        ];
    }
}
