<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $locks = [
            ['1234', 'TS-20', 18000, 950, 'ts20', 'The everyday starter lock', 1],
            ['1235', 'TS-21', 36000, 1850, 'ts21', 'Double the door, double the daily', 2],
            ['1236', 'TS-30', 100000, 5139, 'ts30', 'The crowd favourite', 3],
            ['1237', 'TS-31', 185000, 9507, 'ts31', 'For homes that want more', 4],
            ['1238', 'TS-40', 350000, 17986, 'ts40', 'Serious lock. Serious income.', 5],
            ['1239', 'TS-41', 750000, 38541, 'ts41', 'Premium fingerprint + keypad', 6],
            ['1240', 'TS-50', 1000000, 51389, 'ts50', 'The TSL flagship', 7],
        ];

        foreach ($locks as [$code, $name, $cost, $daily, $key, $tagline, $order]) {
            Product::query()->updateOrCreate(
                ['code' => $code],
                [
                    'kind' => 'lock',
                    'name' => $name,
                    'cost_price' => $cost,
                    'daily_income' => $daily,
                    'duration_days' => 35,
                    'image_key' => $key,
                    'tagline' => $tagline,
                    'sort_order' => $order,
                ]
            );
        }

        $vips = [
            ['vip1', 'VIP1', 'yellow', 100000, 8, 20000, 'ts20', 1],
            ['vip2', 'VIP2', 'blue', 600000, 20, 70000, 'ts21', 2],
            ['vip3', 'VIP3', 'red', 1000000, 55, 170000, 'ts30', 3],
            ['vip4', 'VIP4', 'green', 2500000, 200, 400000, 'ts40', 4],
            ['vip5', 'VIP5', 'crimson', 9000000, 1200, 1000000, 'ts50', 5],
        ];

        foreach ($vips as [$code, $name, $color, $recharge, $members, $salary, $key, $order]) {
            Product::query()->updateOrCreate(
                ['code' => $code],
                [
                    'kind' => 'vip',
                    'name' => $name,
                    'color' => $color,
                    'cost_price' => $recharge,
                    'daily_income' => 0,
                    'member_requirement' => $members,
                    'monthly_salary' => $salary,
                    'duration_days' => 30,
                    'image_key' => $key,
                    'tagline' => $name.' marketing benefit',
                    'sort_order' => $order,
                ]
            );
        }
    }
}
