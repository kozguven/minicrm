<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactInteraction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactInteraction>
 */
class ContactInteractionFactory extends Factory
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
            'user_id' => User::factory(),
            'channel' => fake()->randomElement(['call', 'meeting', 'email', 'whatsapp', 'other']),
            'happened_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'summary' => fake()->sentence(4),
            'notes' => fake()->optional()->paragraph(),
            'follow_up_due_at' => fake()->optional()->dateTimeBetween('now', '+7 days'),
            'follow_up_completed_at' => null,
        ];
    }
}
