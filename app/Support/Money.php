<?php

namespace App\Support;

class Money
{
    public static function ugx(float|int|string $value): string
    {
        return number_format((int) round((float) $value)).' UGX';
    }
}
