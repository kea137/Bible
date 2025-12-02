<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Verse>
 */
class VerseFactory extends Factory
{
    protected $model = \App\Models\Verse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bible = \App\Models\Bible::factory()->create();
        $book = \App\Models\Book::factory()->create(['bible_id' => $bible->id]);
        $chapter = \App\Models\Chapter::factory()->create(['book_id' => $book->id]);

        return [
            'bible_id' => $bible->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'verse_number' => 1,
            'text' => 'Test verse text for automated testing.',
        ];
    }
}
