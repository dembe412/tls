<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Support\Money;
use App\Support\PaymentMethods;
use App\Support\Referrals;
use App\Support\Security\Audit;
use App\Support\Security\ChallengeVault;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    public function checkout(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user->hasOpenLock($product)) {
            return redirect()->route('account')->with(
                'info',
                $product->name.' is already on your owner card.'
            );
        }

        return view('purchases.checkout', [
            'product' => $product,
            'methods' => PaymentMethods::all(),
            'user' => $user,
            'accountBalance' => $user->accountBalance(),
            'rechargeBalance' => $user->rechargeBalance(),
        ]);
    }

    public function store(Request $request, Product $product)
    {
        $user = $request->user();

        if ($user->hasOpenLock($product)) {
            return redirect()->route('account')->with(
                'info',
                $product->name.' is already on your owner card.'
            );
        }

        $request->merge([
            'transaction_id' => strtoupper(preg_replace('/\s+/', '', (string) $request->input('transaction_id')) ?? ''),
        ]);

        $data = $request->validate([
            'payment_source' => ['required', 'string', Rule::in([...Wallet::wallets(), 'mobile_money'])],
            'payment_method' => ['required_if:payment_source,mobile_money', 'nullable', 'string', Rule::in(PaymentMethods::keys())],
            'transaction_id' => [
                'required_if:payment_source,mobile_money',
                'nullable',
                'string',
                'min:4',
                'max:64',
                Rule::unique('purchases', 'transaction_id'),
            ],
            'confirmed' => ['accepted_if:payment_source,mobile_money'],
        ]);

        if ($data['payment_source'] !== 'mobile_money') {
            return $this->payFromBalance($request, $product, $data['payment_source']);
        }

        $method = PaymentMethods::find($data['payment_method']);

        $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'pending',
            'payment_method' => $method['key'],
            'payment_number' => $method['number'],
            'transaction_id' => $data['transaction_id'],
            'payer_name' => $user->profileName(),
            'principal' => $product->cost_price,
            'daily_income' => $product->purchaseDailyIncome(),
            'duration_days' => $product->purchaseDurationDays(),
        ]);

        return redirect()->route('account')->with(
            'success',
            $product->name.' payment sent. A manager will match the transaction and switch the lock on.'
        );
    }

    /**
     * Money already inside TSL, so the lock starts earning straight away.
     */
    private function payFromBalance(Request $request, Product $product, string $wallet)
    {
        $user = $request->user();
        $price = (int) $product->cost_price;

        if (! $user->canPayFrom($wallet, $price)) {
            return back()
                ->withInput()
                ->with('info', 'Your '.strtolower(Wallet::label($wallet)).' is below '.Money::ugx($price).'. Top up or pay by mobile money.');
        }

        $duration = $product->purchaseDurationDays();
        $reference = 'BAL'.strtoupper(Str::random(8));

        $purchase = $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'payment_method' => Wallet::paymentMethodFor($wallet),
            'transaction_id' => $reference,
            'payer_name' => $user->profileName(),
            'principal' => $price,
            'daily_income' => $product->purchaseDailyIncome(),
            'duration_days' => $duration,
            'activated_at' => now(),
            'matures_at' => now()->addDays($duration),
        ]);

        Wallet::debit(
            $user,
            $wallet,
            $price,
            'purchase',
            $product->name,
            $reference,
            $purchase,
        );

        Referrals::payFor($purchase->fresh(['user', 'product']));

        Audit::record('purchase_paid_from_balance', $request, $user, null, [
            'purchase_id' => $purchase->id,
            'wallet' => $wallet,
            'amount' => $price,
            'reference' => $reference,
        ]);

        return redirect()->route('account')->with(
            'success',
            $product->name.' was paid from your '.strtolower(Wallet::label($wallet)).'. Reference '.$reference.'.'
        );
    }

    public function cashOut(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->user_id === $request->user()->id, 403);

        $amount = $purchase->availableToCashOut();
        $minimum = (int) config('payments.min_withdraw', 2000);

        if (! $purchase->isMatured() || $amount <= 0) {
            return back()->with('info', 'This lock is not ready to cash out yet.');
        }

        if ($amount < $minimum) {
            return back()->with(
                'info',
                'Minimum withdraw is '.Money::ugx($minimum).' according to the local Ugandan instructions that govern the financial regulations.'
            );
        }

        $withdrawal = $purchase->withdrawals()->create([
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'status' => 'pending',
            'reference' => 'WD'.strtoupper(Str::random(8)),
            'requested_at' => now(),
            'expires_at' => null,
        ]);

        Audit::record('withdrawal_requested', $request, $request->user(), null, [
            'withdrawal_id' => $withdrawal->id,
            'reference' => $withdrawal->reference,
            'amount' => $amount,
        ]);

        return back()->with(
            'success',
            'Cash out requested. A manager will process '.$withdrawal->reference.'. Minimum withdraw is '.Money::ugx($minimum).'.'
        );
    }
}
