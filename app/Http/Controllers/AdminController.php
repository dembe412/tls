<?php

namespace App\Http\Controllers;

use App\Models\BonusCode;
use App\Models\HeroSetting;
use App\Models\NewsArticle;
use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\User;
use App\Models\Withdrawal;
use App\Support\Media;
use App\Support\Money;
use App\Support\PaymentMethods;
use App\Support\Referrals;
use App\Support\Security\Audit;
use App\Support\Security\ChallengeVault;
use App\Support\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function __construct(private ChallengeVault $challenges) {}

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
            'pendingWithdrawals' => $withdrawals->whereIn('status', ['pending', 'awaiting_approval', 'authorized']),
            'bonusCodes' => BonusCode::query()->with(['assignedTo', 'claimedBy'])->latest()->limit(25)->get(),
            'bonusMinutes' => (int) round(((int) config('support.bonus_code_ttl')) / 60),
            'pendingApprovals' => $this->challenges->pending(),
            'activeValue' => Money::ugx($purchases->where('status', 'active')->sum('principal')),
            'articles' => NewsArticle::query()->with('author')->latest('published_at')->get(),
            'paymentMethods' => PaymentMethods::all(),
            'whatsapp' => PaymentSetting::valueFor('whatsapp', (string) config('support.whatsapp')),
            'hero' => HeroSetting::allSettings(),
        ]);
    }

    public function updateHeroSettings(Request $request)
    {
        $data = $request->validate([
            'badge' => ['nullable', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:150'],
            'title_highlight' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'cta_text' => ['nullable', 'string', 'max:60'],
            'cta_url' => ['nullable', 'string', 'max:255'],
            'secondary_text' => ['nullable', 'string', 'max:60'],
            'secondary_url' => ['nullable', 'string', 'max:255'],
            'trust_1' => ['nullable', 'string', 'max:80'],
            'trust_2' => ['nullable', 'string', 'max:80'],
            'trust_3' => ['nullable', 'string', 'max:80'],
            'show_calculator' => ['nullable'],
            'image' => ['nullable', 'image', 'max:10240'],
            'remove_image' => ['nullable'],
        ]);

        if ($request->boolean('remove_image')) {
            $oldPath = HeroSetting::valueFor('image_path');
            if ($oldPath) {
                Media::delete($oldPath);
            }
            HeroSetting::query()->updateOrCreate(['key' => 'image_path'], ['value' => '']);
        } elseif ($request->hasFile('image')) {
            $oldPath = HeroSetting::valueFor('image_path');
            if ($oldPath) {
                Media::delete($oldPath);
            }
            $path = Media::store($request->file('image'), 'hero');
            HeroSetting::query()->updateOrCreate(['key' => 'image_path'], ['value' => $path]);
        }

        $fields = [
            'badge' => $data['badge'] ?? '',
            'title' => $data['title'],
            'title_highlight' => $data['title_highlight'] ?? '',
            'description' => $data['description'] ?? '',
            'cta_text' => $data['cta_text'] ?? 'Start Earning',
            'cta_url' => $data['cta_url'] ?? '#catalog',
            'secondary_text' => $data['secondary_text'] ?? '',
            'secondary_url' => $data['secondary_url'] ?? '',
            'trust_1' => $data['trust_1'] ?? '',
            'trust_2' => $data['trust_2'] ?? '',
            'trust_3' => $data['trust_3'] ?? '',
            'show_calculator' => $request->has('show_calculator') ? '1' : '0',
        ];

        foreach ($fields as $key => $value) {
            HeroSetting::query()->updateOrCreate(['key' => $key], ['value' => trim((string) $value)]);
        }

        return redirect()->route('admin.index')->with('success', 'Hero section updated successfully.');
    }

    public function updatePaymentMethods(Request $request)
    {
        $data = $request->validate([
            'airtel_number' => ['required', 'string', 'max:30'],
            'airtel_name' => ['required', 'string', 'max:100'],
            'mtn_number' => ['required', 'string', 'max:30'],
            'mtn_name' => ['required', 'string', 'max:100'],
            'whatsapp' => ['nullable', 'string', 'max:30'],
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

        Referrals::payFor($purchase->fresh(['user', 'product']));

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

        $purchase = $user->purchases()->create([
            'product_id' => $product->id,
            'status' => 'active',
            'principal' => $product->cost_price,
            'daily_income' => $product->purchaseDailyIncome(),
            'duration_days' => $product->purchaseDurationDays(),
            'activated_at' => now(),
            'matures_at' => now()->addDays($product->purchaseDurationDays()),
        ]);

        Referrals::payFor($purchase->fresh(['user', 'product']));

        return back()->with('success', $product->name.' marked as bought for '.$user->phone);
    }

    public function creditWallet(Request $request, User $user)
    {
        $data = $request->validate([
            'wallet' => ['required', Rule::in(Wallet::wallets())],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'note' => ['nullable', 'string', 'max:120'],
        ]);

        Wallet::credit(
            $user,
            $data['wallet'],
            $data['amount'],
            $data['wallet'] === Wallet::RECHARGE ? 'recharge' : 'adjustment',
            $data['note'] ?? 'Added by manager',
            'MGR'.strtoupper(Str::random(6)),
        );

        Audit::record('wallet_credited', $request, $request->user(), null, [
            'member_id' => $user->id,
            'wallet' => $data['wallet'],
            'amount' => $data['amount'],
        ]);

        return back()->with(
            'success',
            Money::ugx($data['amount']).' added to the '.strtolower(Wallet::label($data['wallet'])).' of '.$user->profileName().'.'
        );
    }

    public function settle(Request $request, Withdrawal $withdrawal)
    {
        $data = $request->validate([
            'status' => ['required', 'in:paid,rejected'],
        ]);

        if ($data['status'] === 'paid' && ! in_array($withdrawal->status, ['authorized', 'pending', 'awaiting_approval'], true)) {
            return back()->with(
                'info',
                'This withdrawal is not eligible to be marked paid.'
            );
        }

        $withdrawal->update([
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid' ? now() : null,
        ]);

        $withdrawal->challenges()->where('status', 'pending')->update([
            'status' => $data['status'] === 'paid' ? 'approved' : 'rejected',
            'resolved_at' => now(),
        ]);

        Audit::record(
            $data['status'] === 'paid' ? 'withdrawal_paid' : 'withdrawal_rejected',
            $request,
            $request->user(),
            null,
            ['withdrawal_id' => $withdrawal->id, 'reference' => $withdrawal->reference]
        );

        return back()->with('success', $data['status'] === 'paid' ? 'Marked as paid' : 'Request rejected');
    }

    public function storeBonusCode(Request $request)
    {
        $data = $request->validate([
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
            'assigned_user_id' => ['nullable', 'exists:users,id'],
            'note' => ['nullable', 'string', 'max:120'],
        ]);

        $minutes = (int) round(((int) config('support.bonus_code_ttl')) / 60);

        $bonus = BonusCode::query()->create([
            'code' => BonusCode::newCode(),
            'amount' => $data['amount'],
            'created_by_id' => $request->user()->id,
            'assigned_user_id' => $data['assigned_user_id'] ?? null,
            'note' => $data['note'] ?? null,
            'expires_at' => now()->addSeconds((int) config('support.bonus_code_ttl')),
        ]);

        Audit::record('bonus_code_created', $request, $request->user(), null, [
            'code' => $bonus->code,
            'amount' => $bonus->amount,
            'assigned_user_id' => $bonus->assigned_user_id,
        ]);

        return back()->with(
            'success',
            'Bonus code '.$bonus->code.' is ready. Share the link within '.$minutes.' minutes: '.$bonus->shareUrl()
        );
    }
}
