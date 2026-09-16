<?php

namespace App\Http\Controllers;

use App\Models\BonusRedemption;
use App\Models\NewsArticle;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Money;
use App\Support\PaymentMethods;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        $purchases = Purchase::query()->with(['user', 'product'])->latest()->get();
        $withdrawals = Withdrawal::query()->with('user')->latest('requested_at')->get();

        return view('admin.index', [
            'clients' => User::query()->where('role', 'client')->latest()->get(),
            'products' => Product::query()->locks()->get(),
            'vips' => Product::query()->vips()->get(),
            'purchases' => $purchases,
            'withdrawals' => $withdrawals,
            'pendingPurchases' => $purchases->where('status', 'pending'),
            'pendingWithdrawals' => $withdrawals->where('status', 'pending'),
            'pendingBonuses' => BonusRedemption::query()->with('user')->where('status', 'pending')->latest()->get(),
            'activeValue' => Money::ugx($purchases->where('status', 'active')->sum('principal')),
            'articles' => NewsArticle::query()->with('author')->latest('published_at')->get(),
            'paymentMethods' => PaymentMethods::all(),
        ]);
    }

    public function updatePaymentMethods(Request $request)
    {
        $data = $request->validate([
            'airtel_number' => ['required', 'string', 'max:30'],
            'airtel_name' => ['required', 'string', 'max:100'],
            'mtn_number' => ['required', 'string', 'max:30'],
            'mtn_name' => ['required', 'string', 'max:100'],
        ]);

        foreach ($data as $key => $value) {
            PaymentSetting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => trim($value)],
            );
        }

        return back()->with('success', 'Payment details updated.');
    }

    public function activate(Purchase $purchase)
    {
        $purchase->update([
            'status' => 'active',
            'activated_at' => now(),
            'matures_at' => now()->addDays($purchase->duration_days),
        ]);

        return back()->with('success', 'Lock switched on for '.$purchase->user->profileName());
    }

    public function reject(Purchase $purchase)
    {
        abort_unless($purchase->status === 'pending', 404);

        $purchase->update([
            'status' => 'rejected',
        ]);

        return back()->with('success', 'Payment rejected for '.$purchase->user->profileName());
    }

    public function addLock(Request $request, User $user)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $product = Product::query()->findOrFail($data['product_id']);

        $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'principal' => $product->cost_price,
            'daily_income' => $product->purchaseDailyIncome(),
            'duration_days' => $product->purchaseDurationDays(),
            'activated_at' => now(),
            'matures_at' => now()->addDays($product->purchaseDurationDays()),
        ]);

        return back()->with('success', $product->name.' marked as bought for '.$user->phone);
    }

    public function settle(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,rejected'],
        ]);

        $withdrawal->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        return back()->with('success', $data['status'] === 'paid' ? 'Marked as paid' : 'Request rejected');
    }

    public function settleBonus(Request $request, BonusRedemption $redemption)
    {
        abort_unless($redemption->status === 'pending', 404);

        $data = $request->validate([
            'status' => ['required', 'in:applied,rejected'],
        ]);

        $redemption->update([
            'status' => $data['status'],
        ]);

        $label = $data['status'] === 'applied' ? 'applied' : 'rejected';

        return back()->with('success', 'Bonus '.$redemption->code.' '.$label.' for '.$redemption->user->profileName());
    }
}
