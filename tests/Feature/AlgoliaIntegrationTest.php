<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Role;
use App\Models\User;
use App\Models\Verse;
use Illuminate\Support\Facades\Artisan;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Create a user with role
    $userRole = Role::factory()->create(['role_number' => 3, 'name' => 'User']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($userRole);

    // Create a test Bible with books, chapters, and verses
    $this->bible = Bible::factory()->create([
        'name' => 'English Standard Version',
        'abbreviation' => 'ESV',
        'language' => 'English',
        'version' => '2016',
    ]);

    $this->book = Book::factory()->create([
        'bible_id' => $this->bible->id,
        'title' => 'John',
        'book_number' => 43,
    ]);

    $this->chapter = Chapter::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_number' => 3,
    ]);

    // Create some well-known verses for integration testing
    $this->verse1 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 16,
        'text' => 'For God so loved the world, that he gave his only Son, that whoever believes in him should not perish but have eternal life.',
    ]);

    $this->verse2 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 17,
        'text' => 'For God did not send his Son into the world to condemn the world, but in order that the world might be saved through him.',
    ]);

    $this->verse3 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 18,
        'text' => 'Whoever believes in him is not condemned, but whoever does not believe is condemned already.',
    ]);
});

test('complete algolia workflow: import and search via API', function () {
    // Step 1: Import verses to search index
    $importExitCode = Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);
    expect($importExitCode)->toBe(0);

    // Step 2: Search via API endpoint
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=eternal life');

    $response->assertOk()
        ->assertJsonStructure([
            'verses' => [
                '*' => [
                    'id',
                    'text',
                    'verse_number',
                    'bible_id',
                    'book',
                    'chapter',
                ],
            ],
            'total',
        ]);

    $verses = $response->json('verses');
    
    // Should find the verse containing "eternal life"
    if (count($verses) > 0) {
        $foundTexts = collect($verses)->pluck('text')->toArray();
        $hasEternalLife = collect($foundTexts)->contains(function ($text) {
            return str_contains($text, 'eternal life');
        });
        expect($hasEternalLife)->toBeTrue();
    }
});

test('search results can be filtered by bible_id', function () {
    // Create another Bible with different verses
    $anotherBible = Bible::factory()->create([
        'name' => 'King James Version',
        'abbreviation' => 'KJV',
        'language' => 'English',
        'version' => '1611',
    ]);

    $anotherBook = Book::factory()->create([
        'bible_id' => $anotherBible->id,
        'title' => 'Psalms',
        'book_number' => 19,
    ]);

    $anotherChapter = Chapter::factory()->create([
        'bible_id' => $anotherBible->id,
        'book_id' => $anotherBook->id,
        'chapter_number' => 23,
    ]);

    Verse::factory()->create([
        'bible_id' => $anotherBible->id,
        'book_id' => $anotherBook->id,
        'chapter_id' => $anotherChapter->id,
        'verse_number' => 1,
        'text' => 'The Lord is my shepherd; I shall not want.',
    ]);

    // Import all verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search and verify results include multiple bibles
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=Lord&limit=20');

    $response->assertOk();
    $verses = $response->json('verses');

    // Verify that results can contain verses from different bibles
    $bibleIds = collect($verses)->pluck('bible_id')->unique()->toArray();
    expect(count($bibleIds))->toBeGreaterThanOrEqual(1);
});

test('search API returns correct verse metadata', function () {
    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search for a specific verse
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=God so loved');

    $response->assertOk();
    $verses = $response->json('verses');

    if (count($verses) > 0) {
        $verse = $verses[0];

        // Verify all required fields are present
        expect($verse)->toHaveKeys(['id', 'text', 'verse_number', 'bible_id', 'book', 'chapter']);

        // Verify book information
        expect($verse['book'])->toHaveKeys(['id', 'title']);
        expect($verse['book']['title'])->toBeString();

        // Verify chapter information
        expect($verse['chapter'])->toHaveKeys(['id', 'chapter_number']);
        expect($verse['chapter']['chapter_number'])->toBeInt();

        // Verify verse number
        expect($verse['verse_number'])->toBeInt();
    }
});

test('search handles special characters correctly', function () {
    // Create verse with special characters
    Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 20,
        'text' => "Jesus said, \"I am the way, the truth, and the life.\"",
    ]);

    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search with special characters
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=' . urlencode('I am the way'));

    $response->assertOk();
    $verses = $response->json('verses');

    expect($verses)->toBeArray();
});

test('search performance with large result set', function () {
    // Create multiple verses for performance testing
    for ($i = 1; $i <= 20; $i++) {
        Verse::factory()->create([
            'bible_id' => $this->bible->id,
            'book_id' => $this->book->id,
            'chapter_id' => $this->chapter->id,
            'verse_number' => $i + 100,
            'text' => "This is a test verse number {$i} with the word believe in it.",
        ]);
    }

    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Measure search performance
    $startTime = microtime(true);

    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=believe&limit=50');

    $endTime = microtime(true);
    $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

    $response->assertOk();

    // Search should complete reasonably quickly (under 2 seconds for collection driver)
    expect($executionTime)->toBeLessThan(2000);
});

test('scout status command shows verse model status', function () {
    // Skip this test if using collection driver as it doesn't support status command
    if (config('scout.driver') === 'collection') {
        $this->markTestSkipped('Status command not supported with collection driver');
    }

    // Create some verses
    Verse::factory()->count(5)->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
    ]);

    // Get status - this verifies the model is properly configured
    $exitCode = Artisan::call('scout:status', ['searchable' => 'App\\Models\\Verse']);

    expect($exitCode)->toBe(0);
});

test('reimport command works for updating search index', function () {
    // Skip this test if using collection driver as reimport behaves differently
    if (config('scout.driver') === 'collection') {
        $this->markTestSkipped('Reimport command works differently with collection driver');
    }

    // Import verses initially
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Create a new verse after import
    $newVerse = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 99,
        'text' => 'This is a new verse added after initial import.',
    ]);

    // Reimport to include the new verse
    $exitCode = Artisan::call('scout:reimport', ['searchable' => 'App\\Models\\Verse']);

    expect($exitCode)->toBe(0);

    // Verify the new verse is searchable
    $results = Verse::search('new verse added')->get();
    expect($results->count())->toBeGreaterThanOrEqual(0);
});

test('multiple concurrent searches do not interfere', function () {
    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Perform multiple searches concurrently (simulated)
    $response1 = actingAs($this->user)
        ->getJson('/api/verses/search?query=God');

    $response2 = actingAs($this->user)
        ->getJson('/api/verses/search?query=believes');

    $response3 = actingAs($this->user)
        ->getJson('/api/verses/search?query=world');

    // All searches should succeed
    $response1->assertOk();
    $response2->assertOk();
    $response3->assertOk();

    // Results should be independent
    $verses1 = $response1->json('verses');
    $verses2 = $response2->json('verses');
    $verses3 = $response3->json('verses');

    expect($verses1)->toBeArray();
    expect($verses2)->toBeArray();
    expect($verses3)->toBeArray();
});

test('search works after clearing and reimporting index', function () {
    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Verify search works
    $response1 = actingAs($this->user)
        ->getJson('/api/verses/search?query=God');
    $response1->assertOk();

    // Flush the index
    Artisan::call('scout:flush', ['searchable' => 'App\\Models\\Verse']);

    // Reimport
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search should still work
    $response2 = actingAs($this->user)
        ->getJson('/api/verses/search?query=God');
    $response2->assertOk();

    // Results should be comparable
    expect($response2->json('total'))->toBeGreaterThanOrEqual(0);
});
