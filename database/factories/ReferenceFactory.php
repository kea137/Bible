<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reference>
 */
class ReferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'verse_reference' => json_encode([
                fake()->randomElement(['Genesis', 'Exodus', 'John', 'Psalm']).' '.
                fake()->numberBetween(1, 150).':'.
                fake()->numberBetween(1, 50),
            ]),
        ];
    }
}
