<?php

namespace App\Support;

use App\Models\Purchase;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Moves money between a member's two balances and records every movement.
 *
 * Recharge balance holds money the member topped up. Account balance holds
 * what the system paid them — daily interest, bonuses and referral rewards.
 * A product can be bought from either one.
 */
class Wallet
{
    public const ACCOUNT = 'account';

    public const RECHARGE = 'recharge';

    /** Value stored on purchases.payment_method when a balance pays for a lock. */
    public const PAY_ACCOUNT = 'account_balance';

    public const PAY_RECHARGE = 'recharge_balance';

    /**
     * @return array<int, string>
     */
    public static function wallets(): array
    {
        return [self::ACCOUNT, self::RECHARGE];
    }

    /**
     * @return array<int, string>
     */
    public static function paymentMethods(): array
    {
        return [self::PAY_ACCOUNT, self::PAY_RECHARGE];
    }

    public static function paymentMethodFor(string $wallet): string
    {
        return $wallet === self::RECHARGE ? self::PAY_RECHARGE : self::PAY_ACCOUNT;
    }

    public static function fromPaymentMethod(?string $method): ?string
    {
        return match ($method) {
            self::PAY_ACCOUNT => self::ACCOUNT,
            self::PAY_RECHARGE => self::RECHARGE,
            default => null,
        };
    }

    public static function column(string $wallet): string
    {
        return match ($wallet) {
            self::RECHARGE => 'recharge_balance',
            self::ACCOUNT => 'account_balance',
            default => throw new RuntimeException('Unknown wallet: '.$wallet),
        };
    }

    public static function label(string $wallet): string
    {
        return $wallet === self::RECHARGE ? 'Recharge balance' : 'Account balance';
    }

    public static function balance(User $user, string $wallet): int
    {
        return (int) $user->{self::column($wallet)};
    }

    public static function credit(
        User $user,
        string $wallet,
        int $amount,
        string $type,
        ?string $description = null,
        ?string $reference = null,
        ?Purchase $purchase = null,
    ): WalletTransaction {
        return self::move($user, $wallet, abs($amount), $type, $description, $reference, $purchase);
    }

    /**
     * @throws RuntimeException when the balance cannot cover the amount
     */
    public static function debit(
        User $user,
        string $wallet,
        int $amount,
        string $type,
        ?string $description = null,
        ?string $reference = null,
        ?Purchase $purchase = null,
    ): WalletTransaction {
        return self::move($user, $wallet, -abs($amount), $type, $description, $reference, $purchase);
    }

    private static function move(
        User $user,
        string $wallet,
        int $amount,
        string $type,
        ?string $description,
        ?string $reference,
        ?Purchase $purchase,
    ): WalletTransaction {
        $column = self::column($wallet);

        return DB::transaction(function () use ($user, $wallet, $column, $amount, $type, $description, $reference, $purchase) {
            $fresh = User::query()->lockForUpdate()->findOrFail($user->id);
            $balance = (int) $fresh->{$column} + $amount;

            if ($balance < 0) {
                throw new RuntimeException('Not enough money in your '.strtolower(self::label($wallet)).'.');
            }

            $fresh->forceFill([$column => $balance])->save();
            $user->setAttribute($column, $balance);

            return WalletTransaction::query()->create([
                'user_id' => $user->id,
                'purchase_id' => $purchase?->id,
                'wallet' => $wallet,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $balance,
                'reference' => $reference,
                'description' => $description,
            ]);
        });
    }
}
