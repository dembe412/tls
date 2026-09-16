<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function showRegister(Request $request)
    {
        $product = $this->intendedProduct($request);

        return view('auth.register', [
            'product' => $product,
            'inviteHeadline' => $product?->inviteHeadline() ?? 'Your TSL story starts with a free account',
            'inviteBody' => $product?->inviteBody() ?? 'Join thousands unlocking daily earnings. Sign up in seconds — then pick the lock that fits you.',
        ]);
    }

    public function register(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')) ?: null,
            'phone' => User::normalizePhone($request->input('phone')),
        ]);

        $data = $request->validate([
            'name' => [
                'required_without:phone',
                'nullable',
                'string',
                'min:2',
                'max:80',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && User::usernameTaken($value)) {
                        $fail('That username is already taken.');
                    }
                },
            ],
            'phone' => ['required_without:name', 'nullable', 'string', 'min:9', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
        ]);

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $phone ? User::phoneToEmail($phone) : User::emailFromName((string) $name),
            'password' => $data['password'],
            'role' => User::query()->where('role', 'admin')->exists() ? 'client' : 'admin',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isAdmin()
            ? 'Welcome, manager. Your first TSL account is the control desk.'
            : 'You are in. Send the lock payment and a manager will switch it on.';

        if (! $user->isAdmin() && ! empty($data['product_id'])) {
            return redirect()
                ->route('locks.pay', $data['product_id'])
                ->with('success', $message);
        }

        return redirect()->route('account')->with('success', $message);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::findByLogin($data['login']);

        if (! $user || ! Auth::attempt(['email' => $user->email, 'password' => $data['password']], true)) {
            return back()->withErrors(['login' => 'Those details do not match a TSL account.'])->withInput();
        }

        $request->session()->regenerate();

        $home = $user->isAdmin() ? 'admin.index' : 'account';

        return redirect()->intended(route($home))->with('success', 'Welcome back.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'See you soon.');
    }

    private function intendedProduct(Request $request): ?Product
    {
        $id = $request->query('lock') ?? $request->old('product_id');

        return $id ? Product::query()->find($id) : null;
    }
}
