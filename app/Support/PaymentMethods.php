<?php

namespace App\Support;

use App\Models\PaymentSetting;

class PaymentMethods
{
    /**
     * @return array<string, array{key: string, name: string, number: string, account_name: string}>
     */
    public static function all(): array
    {
        return collect(config('payments.methods', []))
            ->map(function (array $method): array {
                $key = $method['key'];

                return [
                    ...$method,
                    'number' => PaymentSetting::valueFor($key.'_number', $method['number']),
                    'account_name' => PaymentSetting::valueFor($key.'_name', $method['account_name']),
                ];
            })
            ->all();
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
