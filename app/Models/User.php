<?php

namespace App\Models;

use App\Support\Media;
use App\Support\Wallet;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'phone', 'email', 'password', 'role', 'avatar_path', 'referral_code', 'referred_by_id', 'phone_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function articles(): HasMany
    {
        return $this->hasMany(NewsArticle::class);
    }

    public function walletTransactions(): HasMany
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function referralEarnings(): HasMany
    {
        return $this->hasMany(ReferralEarning::class);
    }

    public function claimedBonuses(): HasMany
    {
        return $this->hasMany(BonusCode::class, 'claimed_by_id');
    }

    /** Everything this member has been paid on top of their locks. */
    public function rewardsEarned(): int
    {
        return (int) $this->walletTransactions()
            ->whereIn('type', ['referral', 'bonus'])
            ->where('amount', '>', 0)
            ->sum('amount');
    }

    public function referralEarningsTotal(): int
    {
        return (int) $this->referralEarnings()->sum('amount');
    }

    public function accountBalance(): int
    {
        return (int) $this->account_balance;
    }

    public function rechargeBalance(): int
    {
        return (int) $this->recharge_balance;
    }

    public function canPayFrom(string $wallet, int $amount): bool
    {
        return Wallet::balance($this, $wallet) >= $amount;
    }

    public function staffDevices(): HasMany
    {
        return $this->hasMany(StaffDevice::class);
    }

    public function persistentLogins(): HasMany
    {
        return $this->hasMany(PersistentLogin::class);
    }

    public function securityLogs(): HasMany
    {
        return $this->hasMany(SecurityLog::class);
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(self::class, 'referred_by_id');
    }

    public function levelAMembers(): HasMany
    {
        return $this->hasMany(self::class, 'referred_by_id');
    }

    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            if (! $user->referral_code) {
                $user->referral_code = static::uniqueReferralCode();
            }
        });
    }

    public static function uniqueReferralCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (static::query()->where('referral_code', $code)->exists());

        return $code;
    }

    public function inviteUrl(): string
    {
        return route('register', ['ref' => $this->referral_code]);
    }

    public function levelBMembers()
    {
        return static::query()->whereIn(
            'referred_by_id',
            $this->levelAMembers()->select('id')
        );
    }

    public function levelCMembers()
    {
        return static::query()->whereIn(
            'referred_by_id',
            $this->levelBMembers()->select('id')
        );
    }

    public function abcMemberCount(): int
    {
        return $this->levelAMembers()->count()
            + $this->levelBMembers()->count()
            + $this->levelCMembers()->count();
    }

    /**
     * The highest team level this member has actually filled, shown on the
     * account page as "ABC level".
     */
    public function abcLevelLabel(): string
    {
        return match (true) {
            $this->levelCMembers()->exists() => 'C',
            $this->levelBMembers()->exists() => 'B',
            $this->levelAMembers()->exists() => 'A',
            default => 'None',
        };
    }

    public function isInvestor(): bool
    {
        return $this->purchases()
            ->whereIn('status', ['pending', 'active'])
            ->exists();
    }

    /**
     * Money the member has actually put into TSL. Locks bought from a balance
     * are left out because that money was already counted when it came in.
     */
    public function cumulativeRecharge(): int
    {
        $sentIn = (int) $this->purchases()
            ->whereIn('status', ['pending', 'active'])
            ->where(fn ($query) => $query
                ->whereNull('payment_method')
                ->orWhereNotIn('payment_method', Wallet::paymentMethods())
            )
            ->sum('principal');

        $toppedUp = (int) $this->walletTransactions()
            ->where('type', 'recharge')
            ->where('amount', '>', 0)
            ->sum('amount');

        return $sentIn + $toppedUp;
    }

    public function vipLevel(): int
    {
        if (! $this->isInvestor()) {
            return -1;
        }

        $hasPurchase = $this->purchases()->where('status', 'active')->exists();
        $recharge = $this->cumulativeRecharge();
        $members = $this->abcMemberCount();
        $level = 0;

        if ($hasPurchase) {
            foreach (Product::query()->vips()->orderByDesc('sort_order')->get() as $vip) {
                if ($recharge >= (int) $vip->cost_price && $members >= (int) $vip->member_requirement) {
                    $level = max($level, (int) $vip->sort_order);
                }
            }
        }

        return $level;
    }

    public function vipRankLabel(): string
    {
        $level = $this->vipLevel();

        return $level < 0 ? 'Ordinary' : 'VIP '.$level;
    }

    public function profileName(): string
    {
        $name = trim((string) $this->name);

        if ($name !== '' && ! in_array(strtolower($name), ['tsl member', 'member'], true)) {
            return $name;
        }

        return $this->isAdmin() ? 'TSL Manager' : ($this->phone ?: 'TSL member');
    }

    public function initials(): string
    {
        $parts = preg_split('/\s+/', $this->profileName()) ?: [];
        $letters = collect($parts)->filter()->map(fn ($part) => mb_substr($part, 0, 1))->take(2)->implode('');

        return strtoupper($letters ?: mb_substr((string) ($this->phone ?: 'TS'), -2));
    }

    public function avatarUrl(): ?string
    {
        return Media::url($this->avatar_path);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function hasOpenLock(Product $product): bool
    {
        return $this->purchases()
            ->where('product_id', $product->id)
            ->whereIn('status', ['pending', 'active'])
            ->exists();
    }

    public static function phoneToEmail(string $phone): string
    {
        return preg_replace('/\D/', '', $phone).'@tsl.app';
    }

    public static function emailFromName(string $name): string
    {
        $slug = Str::slug($name) ?: 'member';
        $email = $slug.'@tsl.app';
        $suffix = 1;

        while (static::query()->where('email', $email)->exists()) {
            $email = $slug.$suffix.'@tsl.app';
            $suffix++;
        }

        return $email;
    }

    public static function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D/', '', (string) $phone) ?? '';

        return $digits !== '' ? $digits : null;
    }

    public static function findByLogin(string $identifier): ?self
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $digits = static::normalizePhone($identifier);

        if ($digits && strlen($digits) >= 9) {
            $match = static::query()->where('phone', $digits)->first();

            if ($match) {
                return $match;
            }
        }

        return static::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($identifier)])
            ->whereNotIn('name', ['TSL member', 'member'])
            ->first();
    }

    public static function usernameTaken(string $name, ?int $exceptId = null): bool
    {
        $name = trim($name);

        if ($name === '' || in_array(strtolower($name), ['tsl member', 'member'], true)) {
            return false;
        }

        return static::query()
            ->whereRaw('lower(name) = ?', [mb_strtolower($name)])
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
