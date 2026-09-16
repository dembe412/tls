<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Support\Money;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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

        return view('account.index', [
            'user' => $user,
            'purchases' => $purchases,
            'withdrawals' => $withdrawals,
            'products' => Product::query()->locks()->get(),
            'vips' => Product::query()->vips()->get(),
            'totalEarned' => Money::ugx($purchases->sum(fn ($p) => $p->earnedSoFar())),
            'activeValue' => Money::ugx(
                $purchases->where('status', 'active')->sum('principal')
            ),
        ]);
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
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user->name = $data['name'];
        $user->phone = $data['phone'] ?? null;

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $request->file('avatar')->store('avatars', 'public');
        }

        $user->save();

        return back()->with('success', 'Your profile is updated. Sign in with your username or phone.');
    }
}
