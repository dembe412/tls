<?php

namespace App\Support;

use App\Models\Purchase;
use App\Models\ReferralEarning;
use App\Models\User;

/**
 * Pays the people above a buyer when their lock or VIP is switched on.
 *
 * The member who invited the buyer is Level A, whoever invited them is
 * Level B, and one step further up is Level C. Each level earns a share of
 * the price, and VIP products pay Level A a larger share than locks do.
 */
class Referrals
{
    /**
     * Pays every level above this purchase once. Calling it again for the
     * same purchase does nothing, so it is safe on re-activation.
     *
     * @return int total paid out
     */
    public static function payFor(Purchase $purchase): int
    {
        $buyer = $purchase->user;
        $price = (int) $purchase->principal;

        if (! $buyer || $price <= 0) {
            return 0;
        }

        $kind = $purchase->product?->isVip() ? 'vip' : 'lock';
        $paid = 0;
        $upline = $buyer->referrer;

        foreach (['A', 'B', 'C'] as $level) {
            if (! $upline) {
                break;
            }

            $paid += self::pay($upline, $buyer, $purchase, $level, self::rate($level, $kind), $price);
            $upline = $upline->referrer;
        }

        return $paid;
    }

    public static function rate(string $level, string $kind): int
    {
        return (int) (config("support.referral_rates.{$level}.{$kind}") ?? 0);
    }

    /**
     * @return array<string, array{lock: int, vip: int}>
     */
    public static function rates(): array
    {
        return config('support.referral_rates', []);
    }

    private static function pay(User $earner, User $buyer, Purchase $purchase, string $level, int $rate, int $price): int
    {
        $amount = (int) floor($price * $rate / 100);

        if ($amount <= 0 || $earner->id === $buyer->id) {
            return 0;
        }

        $alreadyPaid = ReferralEarning::query()
            ->where('purchase_id', $purchase->id)
            ->where('user_id', $earner->id)
            ->exists();

        if ($alreadyPaid) {
            return 0;
        }

        ReferralEarning::query()->create([
            'user_id' => $earner->id,
            'member_id' => $buyer->id,
            'purchase_id' => $purchase->id,
            'level' => $level,
            'rate_percent' => $rate,
            'amount' => $amount,
        ]);

        Wallet::credit(
            $earner,
            Wallet::ACCOUNT,
            $amount,
            'referral',
            'Level '.$level.' commission from '.$buyer->profileName(),
            $purchase->transaction_id,
            $purchase,
        );

        return $amount;
    }
}
