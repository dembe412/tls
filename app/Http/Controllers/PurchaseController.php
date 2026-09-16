<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;
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
            'payment_method' => ['required', 'string', Rule::in(PaymentMethods::keys())],
            'transaction_id' => [
                'required',
                'string',
                'min:4',
                'max:64',
                Rule::unique('purchases', 'transaction_id'),
            ],
            'confirmed' => ['accepted'],
        ]);

        $method = PaymentMethods::find($data['payment_method']);
        $transactionId = $data['transaction_id'];

        $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'pending',
            'payment_method' => $method['key'],
            'payment_number' => $method['number'],
            'transaction_id' => $transactionId,
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

    public function cashOut(Request $request, Purchase $purchase)
    {
        abort_unless($purchase->user_id === $request->user()->id, 403);

        $amount = $purchase->availableToCashOut();

        if (! $purchase->isMatured() || $amount <= 0) {
            return back()->with('info', 'This lock is not ready to cash out yet.');
        }

        $purchase->withdrawals()->create([
            'user_id' => $request->user()->id,
            'amount' => $amount,
            'status' => 'pending',
            'requested_at' => now(),
        ]);

        return back()->with('success', 'Cash out requested. A manager will settle it.');
    }
}
