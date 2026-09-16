<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

#[Fillable(['name', 'phone', 'email', 'password', 'role', 'avatar_path'])]
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

    public function bonusRedemptions(): HasMany
    {
        return $this->hasMany(BonusRedemption::class);
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
        return $this->avatar_path ? asset('storage/'.$this->avatar_path) : null;
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
            'password' => 'hashed',
        ];
    }
}
