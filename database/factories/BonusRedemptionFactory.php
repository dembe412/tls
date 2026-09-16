<?php

namespace Database\Factories;

use App\Models\BonusRedemption;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BonusRedemption>
 */
class BonusRedemptionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => strtoupper(fake()->unique()->bothify('TSL###')),
            'status' => 'pending',
        ];
    }
}
