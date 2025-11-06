<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('share page loads with verse data from query parameters', function () {
    $response = $this->get('/share?reference=John 3:16&text=For God so loved the world');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Share')
        ->where('verseReference', 'John 3:16')
        ->where('verseText', 'For God so loved the world')
    );
});

test('share page loads with verse data from database', function () {
    // Create test data
    $bible = Bible::factory()->create();
    $book = Book::factory()->create([
        'bible_id' => $bible->id,
        'title' => 'John',
        'book_number' => 43,
    ]);
    $chapter = Chapter::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_number' => 3,
    ]);
    $verse = Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'verse_number' => 16,
        'text' => 'For God so loved the world',
    ]);

    $response = $this->get("/share?verseId={$verse->id}");

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Share')
        ->where('verseReference', 'John 3:16')
        ->where('verseText', 'For God so loved the world')
        ->where('verseId', $verse->id)
    );
});

test('share page provides background images when Pexels API is configured', function () {
    Config::set('services.pexels.api_key', 'test-api-key');

    Http::fake([
        'api.pexels.com/*' => Http::response([
            'photos' => [
                [
                    'id' => 1,
                    'src' => [
                        'large' => 'https://example.com/large.jpg',
                        'small' => 'https://example.com/small.jpg',
                    ],
                    'photographer' => 'Test Photographer',
                    'photographer_url' => 'https://example.com/photographer',
                    'alt' => 'Test image',
                ],
            ],
        ], 200),
    ]);

    $response = $this->get('/share?reference=Test&text=Test verse');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Share')
        ->has('backgroundImages', 1)
        ->where('backgroundImages.0.photographer', 'Test Photographer')
    );
});

test('share page handles missing Pexels API key gracefully', function () {
    Config::set('services.pexels.api_key', '');

    $response = $this->get('/share?reference=Test&text=Test verse');

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Share')
        ->where('backgroundImages', [])
    );
});
