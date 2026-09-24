<?php

namespace App\Support;

use App\Models\User;

class Phone
{
    public static function normalize(?string $phone): ?string
    {
        return User::normalizePhone($phone);
    }

    public static function international(?string $phone): ?string
    {
        $digits = static::normalize($phone);

        if (! $digits) {
            return null;
        }

        if (str_starts_with($digits, '256')) {
            return '+'.$digits;
        }

        if (str_starts_with($digits, '0')) {
            return '+256'.substr($digits, 1);
        }

        return '+256'.$digits;
    }
}
