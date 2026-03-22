<?php

namespace Database\Factories;

use App\Models\OpportunityStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OpportunityStage>
 */
class OpportunityStageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'position' => fake()->numberBetween(1, 10),
            'is_won' => false,
        ];
    }
}
