<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class PaymentSetting extends Model
{
    public static function valueFor(string $key, string $default = ''): string
    {
        return (string) (static::query()->where('key', $key)->value('value') ?? $default);
    }
}
