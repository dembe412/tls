<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use App\Support\HumanCheck;
use App\Support\Phone;
use App\Support\Security\Audit;
use App\Support\Security\PersistentSessions;
use App\Support\Security\StaffAuthenticator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private StaffAuthenticator $staff,
        private PersistentSessions $persistent,
    ) {}

    public function showRegister(Request $request)
    {
        $product = $this->intendedProduct($request);

        return view('auth.register', [
            'product' => $product,
            'inviteHeadline' => $product?->inviteHeadline() ?? 'Your TSL story starts with a free account',
            'inviteBody' => $product?->inviteBody() ?? 'Join thousands unlocking daily earnings. Sign up in seconds — then pick the lock that fits you.',
            'referrer' => $this->referrerFrom($request),
            'humanSeed' => HumanCheck::issue($request),
        ]);
    }

    public function register(Request $request)
    {
        HumanCheck::validate($request);

        $request->merge([
            'name' => trim((string) $request->input('name')) ?: null,
            'phone' => Phone::normalize($request->input('phone')),
            'ref' => strtoupper(trim((string) ($request->input('ref') ?: $request->query('ref')))),
        ]);

        $data = $request->validate([
            'name' => [
                'nullable',
                'string',
                'min:2',
                'max:80',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (is_string($value) && $value !== '' && User::usernameTaken($value)) {
                        $fail('That username is already taken.');
                    }
                },
            ],
            'phone' => ['required_without:name', 'nullable', 'string', 'min:9', 'max:20', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'product_id' => ['nullable', 'integer', Rule::exists('products', 'id')],
            'ref' => ['nullable', 'string', 'max:12'],
        ]);

        $name = $data['name'] ?? null;
        $phone = $data['phone'] ?? null;
        $referrer = $this->referrerFrom($request);

        $user = User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $phone ? User::phoneToEmail($phone) : User::emailFromName((string) $name),
            'password' => $data['password'],
            'role' => User::query()->where('role', 'admin')->exists() ? 'client' : 'admin',
            'referred_by_id' => $referrer?->id,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        $message = $user->isAdmin()
            ? 'Welcome, manager.'
            : 'You are in. Send the lock payment and a manager will switch it on.';

        if ($user->isAdmin()) {
            return redirect()->route('admin.index')->with('success', $message);
        }

        if (! empty($data['product_id'])) {
            return redirect()
                ->route('locks.pay', $data['product_id'])
                ->with('success', $message);
        }

        return redirect()->intended(route('account'))->with('success', $message);
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string'],
            'keep_signed_in' => ['sometimes', 'boolean'],
        ]);

        $this->ensureIsNotRateLimited($request, $data['login']);

        $user = User::findByLogin($data['login']);
        $keep = $request->boolean('keep_signed_in');

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            RateLimiter::hit($this->ipThrottleKey($request), 60);
            RateLimiter::hit($this->identifierThrottleKey($request, $data['login']), 60);
            Audit::record('login_failed', $request, $user, null, [
                'login_hash' => $this->identifierHash($data['login']),
            ]);

            throw ValidationException::withMessages([
                'login' => 'Those details do not match a TSL account.',
            ]);
        }

        RateLimiter::clear($this->ipThrottleKey($request));
        RateLimiter::clear($this->identifierThrottleKey($request, $data['login']));

        Auth::login($user, $keep);
        $request->session()->regenerate();

        if ($user->isAdmin()) {
            return redirect()->intended(route('admin.index'))->with('success', 'Welcome back, manager.');
        }

        return redirect()->intended(route('account'))->with('success', 'Welcome back.');
    }

    public function logout(Request $request)
    {
        $this->persistent->forget($request);
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

    private function referrerFrom(Request $request): ?User
    {
        $code = strtoupper(trim((string) ($request->input('ref') ?: $request->query('ref') ?: $request->old('ref'))));

        if ($code === '') {
            return null;
        }

        return User::query()->where('referral_code', $code)->first();
    }

    private function ensureIsNotRateLimited(Request $request, string $identifier): void
    {
        $maxAttempts = (int) config('security.max_login_attempts', 5);

        if (! RateLimiter::tooManyAttempts($this->ipThrottleKey($request), $maxAttempts)
            && ! RateLimiter::tooManyAttempts($this->identifierThrottleKey($request, $identifier), $maxAttempts)) {
            return;
        }

        throw ValidationException::withMessages([
            'login' => 'Too many sign-in attempts. Try again in a minute.',
        ]);
    }

    private function ipThrottleKey(Request $request): string
    {
        return 'login:'.$request->ip();
    }

    private function identifierThrottleKey(Request $request, string $identifier): string
    {
        return 'login:'.$request->ip().':'.$this->identifierHash($identifier);
    }

    private function identifierHash(string $identifier): string
    {
        return hash_hmac('sha256', mb_strtolower(trim($identifier)), (string) config('app.key'));
    }
}
