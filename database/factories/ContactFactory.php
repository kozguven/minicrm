<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'owner_user_id' => null,
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'lead_source' => fake()->randomElement(['website', 'referral', 'event', 'other']),
            'lead_status' => fake()->randomElement(['new', 'qualified', 'contacted']),
            'priority' => fake()->randomElement(['low', 'medium', 'high']),
            'last_contacted_at' => fake()->optional()->dateTimeBetween('-10 days', 'now'),
        ];
    }
}
