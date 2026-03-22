<?php

namespace Database\Factories;

use App\Models\CrmTask;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CrmTask>
 */
class CrmTaskFactory extends Factory
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
            'title' => fake()->sentence(4),
            'due_at' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'completed_at' => null,
        ];
    }
}
