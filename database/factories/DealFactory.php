<?php

namespace Database\Factories;

use App\Models\Deal;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Deal>
 */
class DealFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'closed_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
