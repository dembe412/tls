<?php

namespace App\Models;

use App\Support\Media;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'code',
        'kind',
        'name',
        'color',
        'cost_price',
        'daily_income',
        'member_requirement',
        'monthly_salary',
        'duration_days',
        'image_key',
        'image_path',
        'tagline',
        'sort_order',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function scopeLocks($query)
    {
        return $query->where('kind', 'lock')->orderBy('sort_order');
    }

    public function scopeVips($query)
    {
        return $query->where('kind', 'vip')->orderBy('sort_order');
    }

    public function isVip(): bool
    {
        return $this->kind === 'vip';
    }

    public function salaryLabel(): string
    {
        return Money::ugx($this->monthly_salary ?? 0);
    }

    public function vipRequirement(): string
    {
        return 'Invest in any product. Cumulative recharge of '.$this->priceLabel().' and ABC Level members '.$this->member_requirement.'.';
    }

    public function colorHex(): string
    {
        return match ($this->color) {
            'yellow' => '#F4E000',
            'blue' => '#2F7BFF',
            'red' => '#E31C24',
            'green' => '#3CB44B',
            'crimson' => '#C41E24',
            default => '#F4E000',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function vipColors(): array
    {
        return [
            'yellow' => 'Yellow',
            'blue' => 'Blue',
            'red' => 'Red',
            'green' => 'Green',
            'crimson' => 'Crimson',
        ];
    }

    public function priceLabel(): string
    {
        return Money::ugx($this->cost_price);
    }

    public function dailyLabel(): string
    {
        return Money::ugx($this->daily_income);
    }

    public function purchaseDailyIncome(): int
    {
        if ($this->isVip()) {
            return max(1, (int) round(((int) $this->monthly_salary) / 30));
        }

        return (int) $this->daily_income;
    }

    public function purchaseDurationDays(): int
    {
        return $this->isVip() ? 30 : (int) $this->duration_days;
    }

    public function imageUrl(): string
    {
        if ($this->image_path) {
            return Media::url($this->image_path) ?? asset('images/locks/ts20.svg');
        }

        $fallback = public_path('images/locks/'.$this->image_key.'.svg');
        if ($this->image_key && file_exists($fallback)) {
            return asset('images/locks/'.$this->image_key.'.svg');
        }

        return asset('images/locks/ts20.svg');
    }

    public function inviteHeadline(): string
    {
        return match ($this->image_key) {
            'ts20' => 'This starter lock is waiting for you',
            'ts21' => 'A smarter door — and daily interest',
            'ts30' => 'Your first serious TSL lock is one signup away',
            'ts31' => 'Own the lock people notice',
            'ts40' => 'This is the lock that changes a month',
            'ts41' => 'Premium steel. Premium daily interest.',
            'ts50' => 'The flagship is reserved for members',
            default => $this->name.' is waiting for you',
        };
    }

    public function inviteBody(): string
    {
        if ($this->isVip()) {
            return "Create a free TSL account to claim {$this->name}. Send {$this->priceLabel()} and earn {$this->salaryLabel()} each month — paid on the 1st.";
        }

        return "Create a free TSL account to claim {$this->name}. It pays {$this->dailyLabel()} every day — cash out any day once you reach the minimum.";
    }
}
