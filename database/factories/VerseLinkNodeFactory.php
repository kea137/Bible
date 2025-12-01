<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VerseLinkNode>
 */
class VerseLinkNodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'canvas_id' => \App\Models\VerseLinkCanvas::factory(),
            'verse_id' => \App\Models\Verse::factory(),
            'position_x' => fake()->numberBetween(0, 1000),
            'position_y' => fake()->numberBetween(0, 1000),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
