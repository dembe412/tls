<?php

namespace App\Http\Controllers;

use App\Models\PaymentSetting;
use App\Models\Product;
use App\Models\User;
use App\Support\Media;
use App\Support\Money;
use App\Support\Referrals;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return view('account.guest');
        }

        $purchases = $user->purchases()->with(['product', 'withdrawals'])->latest()->get();
        $withdrawals = $user->withdrawals()->latest('requested_at')->get();
        $active = $purchases->where('status', 'active');
        $transactions = $user->walletTransactions()->latest()->limit(40)->get();

        return view('account.index', [
            'user' => $user,
            'purchases' => $purchases,
            'withdrawals' => $withdrawals,
            'whatsapp' => $this->whatsappUrl(),
            'whatsappLabel' => $this->whatsappLabel(),
            'minWithdraw' => (int) config('payments.min_withdraw', 2000),
            'accountBalance' => Money::ugx($user->accountBalance()),
            'rechargeBalance' => Money::ugx($user->rechargeBalance()),
            'transactions' => $transactions,
            'rechargeHistory' => $transactions->where('type', 'recharge'),
            'totalRecharge' => Money::ugx($user->cumulativeRecharge()),
            'totalWithdrawn' => Money::ugx($withdrawals->where('status', '!=', 'rejected')->sum('amount')),
            'dailyEarned' => Money::ugx($active->sum('daily_income')),
            'abcLevel' => $user->abcLevelLabel(),
            'levelA' => $user->levelAMembers()->count(),
            'levelB' => $user->levelBMembers()->count(),
            'levelC' => $user->levelCMembers()->count(),
            'inviteUrl' => $user->inviteUrl(),
            'referralRates' => Referrals::rates(),
            'referralHistory' => $user->referralEarnings()->with('member')->latest()->limit(20)->get(),
            'invited' => $user->levelAMembers()->latest()->get(),
            'referralTotal' => Money::ugx($user->referralEarningsTotal()),
            'rewardsTotal' => Money::ugx($user->rewardsEarned()),
            'claimedBonuses' => $user->claimedBonuses()->latest('claimed_at')->get(),
            'vipProgress' => $this->vipProgress($user),
        ]);
    }

    /**
     * How far this member is from the next VIP card.
     *
     * @return array{next: ?Product, percent: int, recharge: int, members: int}
     */
    private function vipProgress(User $user): array
    {
        $recharge = $user->cumulativeRecharge();
        $members = $user->abcMemberCount();

        $next = Product::query()
            ->vips()
            ->where('sort_order', '>', max($user->vipLevel(), 0))
            ->orderBy('sort_order')
            ->first();

        if (! $next) {
            return ['next' => null, 'percent' => 100, 'recharge' => $recharge, 'members' => $members];
        }

        $needRecharge = max(1, (int) $next->cost_price);
        $needMembers = max(1, (int) $next->member_requirement);

        $percent = (int) floor(min(
            $recharge / $needRecharge,
            $members / $needMembers,
        ) * 100);

        return [
            'next' => $next,
            'percent' => max(0, min(100, $percent)),
            'recharge' => $recharge,
            'members' => $members,
        ];
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();
        abort_unless($user, 403);

        $request->merge([
            'name' => trim((string) $request->input('name')),
            'phone' => User::normalizePhone($request->input('phone')),
        ]);

        $data = $request->validateWithBag('profile', [
            'name' => [
                'required',
                'string',
                'min:2',
                'max:80',
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if (User::usernameTaken((string) $value, $user->id)) {
                        $fail('That username is already taken.');
                    }
                },
            ],
            'phone' => [
                'nullable',
                'string',
                'min:9',
                'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp'],
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;
        $oldAvatar = null;

        if ($request->hasFile('avatar')) {
            $oldAvatar = $user->avatar_path;
            $user->avatar_path = Media::store($request->file('avatar'), 'avatars');
        }

        $user->save();
        Media::delete($oldAvatar);

        return back()->with('success', 'Your profile is updated. Sign in with your username or phone.');
    }

    public static function whatsappUrl(): ?string
    {
        $number = self::supportNumber();

        return $number ? 'https://wa.me/'.$number : null;
    }

    /**
     * The support line as a member should read it, e.g. "+256 740 602783".
     */
    public static function whatsappLabel(): ?string
    {
        $number = self::supportNumber();

        if (! $number) {
            return null;
        }

        if (strlen($number) === 12 && str_starts_with($number, '256')) {
            return '+256 '.substr($number, 3, 3).' '.substr($number, 6);
        }

        return '+'.$number;
    }

    /**
     * Digits only, in international form, or null when support is not set up.
     */
    private static function supportNumber(): ?string
    {
        $number = preg_replace('/\D/', '', (string) (
            PaymentSetting::valueFor('whatsapp', '')
            ?: config('support.whatsapp')
        ));

        if (! $number) {
            return null;
        }

        if (str_starts_with($number, '0')) {
            $number = '256'.substr($number, 1);
        }

        return $number;
    }
}
