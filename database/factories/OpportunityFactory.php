<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Opportunity;
use App\Models\OpportunityStage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Opportunity>
 */
class OpportunityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'owner_user_id' => null,
            'opportunity_stage_id' => OpportunityStage::factory(),
            'title' => fake()->sentence(3),
            'value' => fake()->randomFloat(2, 1000, 50000),
            'probability' => fake()->numberBetween(15, 85),
            'expected_close_date' => fake()->optional()->date(),
            'next_step' => fake()->optional()->sentence(3),
            'next_step_due_at' => fake()->optional()->dateTimeBetween('now', '+2 weeks'),
            'health_status' => fake()->randomElement(['commit', 'watch', 'risk']),
        ];
    }
}
