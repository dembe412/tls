<?php

namespace App\Support;

class PaymentMethods
{
    /**
     * @return array<string, array{key: string, name: string, number: string, account_name: string}>
     */
    public static function all(): array
    {
        return config('payments.methods', []);
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return array{key: string, name: string, number: string, account_name: string}|null
     */
    public static function find(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
