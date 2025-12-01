<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VerseSuggestion>
 */
class VerseSuggestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'verse_id' => \App\Models\Verse::factory(),
            'score' => fake()->randomFloat(2, 0, 1),
            'reasons' => [
                [
                    'type' => 'cross_reference',
                    'description' => 'Related to a verse you highlighted',
                ],
            ],
            'dismissed' => false,
            'clicked' => false,
        ];
    }
}
