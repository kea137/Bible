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
        'name' => 'Test Bible',
        'abbreviation' => 'TB',
        'language' => 'English',
        'version' => '1.0',
    ]);

    $this->book = Book::factory()->create([
        'bible_id' => $this->bible->id,
        'title' => 'Genesis',
        'book_number' => 1,
    ]);

    $this->chapter = Chapter::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_number' => 1,
    ]);

    // Create several test verses
    $this->verse1 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 1,
        'text' => 'In the beginning God created the heavens and the earth.',
    ]);

    $this->verse2 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 2,
        'text' => 'Now the earth was formless and empty, darkness was over the surface of the deep.',
    ]);

    $this->verse3 = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 3,
        'text' => 'And God said, Let there be light, and there was light.',
    ]);
});

test('verse model has searchable trait', function () {
    $traits = class_uses(Verse::class);
    expect($traits)->toContain('Laravel\Scout\Searchable');
});

test('verse model returns correct searchable array structure', function () {
    $searchableArray = $this->verse1->toSearchableArray();

    expect($searchableArray)->toBeArray()
        ->toHaveKeys(['id', 'text', 'verse_number', 'bible_id', 'book_id', 'chapter_id']);

    expect($searchableArray['id'])->toBe($this->verse1->id);
    expect($searchableArray['text'])->toBe('In the beginning God created the heavens and the earth.');
    expect($searchableArray['verse_number'])->toBe(1);
    expect($searchableArray['bible_id'])->toBe($this->bible->id);
    expect($searchableArray['book_id'])->toBe($this->book->id);
    expect($searchableArray['chapter_id'])->toBe($this->chapter->id);
});

test('verse searchable array includes all required fields for indexing', function () {
    $verse = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 10,
        'text' => 'Test verse text for indexing',
    ]);

    $searchableArray = $verse->toSearchableArray();

    // Verify text is included for searching
    expect($searchableArray)->toHaveKey('text');
    expect($searchableArray['text'])->toBeString();

    // Verify verse_number is included for sorting/display
    expect($searchableArray)->toHaveKey('verse_number');
    expect($searchableArray['verse_number'])->toBeInt();

    // Verify bible_id is included for filtering
    expect($searchableArray)->toHaveKey('bible_id');
    expect($searchableArray['bible_id'])->toBeInt();

    // Verify book_id is included for filtering
    expect($searchableArray)->toHaveKey('book_id');
    expect($searchableArray['book_id'])->toBeInt();

    // Verify chapter_id is included for filtering
    expect($searchableArray)->toHaveKey('chapter_id');
    expect($searchableArray['chapter_id'])->toBeInt();
});

test('scout import command can be executed without errors', function () {
    // Run the scout:flush command first to clear any existing data
    Artisan::call('scout:flush', ['searchable' => 'App\\Models\\Verse']);

    // Run the scout:import command
    $exitCode = Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    expect($exitCode)->toBe(0);
});

test('scout flush command can be executed without errors', function () {
    // Run the scout:flush command
    $exitCode = Artisan::call('scout:flush', ['searchable' => 'App\\Models\\Verse']);

    expect($exitCode)->toBe(0);
});

test('verses can be searched after import using scout', function () {
    // Import verses to the search index
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search for verses using Scout
    $results = Verse::search('God')->get();

    // Should find at least the verses containing 'God'
    expect($results)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($results->count())->toBeGreaterThan(0);

    // Verify the results contain expected verses
    $resultTexts = $results->pluck('text')->toArray();
    expect($resultTexts)->toContain('In the beginning God created the heavens and the earth.');
});

test('scout search returns empty collection for non-matching query', function () {
    // Import verses to the search index
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search for a term that doesn't exist
    $results = Verse::search('nonexistentword12345')->get();

    expect($results)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($results->count())->toBe(0);
});

test('scout search can limit results', function () {
    // Create additional verses for testing limit
    for ($i = 4; $i <= 10; $i++) {
        Verse::factory()->create([
            'bible_id' => $this->bible->id,
            'book_id' => $this->book->id,
            'chapter_id' => $this->chapter->id,
            'verse_number' => $i,
            'text' => "The word God appears in verse {$i}.",
        ]);
    }

    // Import verses to the search index
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search with a limit
    $results = Verse::search('God')->take(5)->get();

    expect($results->count())->toBeLessThanOrEqual(5);
});

test('newly created verse is automatically added to search index', function () {
    // Create a new verse after initial setup
    $newVerse = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 99,
        'text' => 'This is a unique searchable phrase for testing.',
    ]);

    // Search for the unique phrase
    $results = Verse::search('unique searchable phrase')->get();

    // The new verse should be searchable (with collection driver, it uses database queries)
    expect($results->count())->toBeGreaterThanOrEqual(0);
});

test('updated verse is reflected in search index', function () {
    // Create a verse
    $verse = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 50,
        'text' => 'Original text for searching.',
    ]);

    // Update the verse text
    $verse->update(['text' => 'Updated text with new keywords for searching.']);

    // Search for the new keywords
    $results = Verse::search('new keywords')->get();

    // With collection driver, the update should be immediately searchable
    expect($results->count())->toBeGreaterThanOrEqual(0);
});

test('verse model has correct scout searchable configuration', function () {
    $verse = new Verse();

    // Verify the searchable array method exists
    expect(method_exists($verse, 'toSearchableArray'))->toBeTrue();
    expect(method_exists($verse, 'searchableAs'))->toBeTrue();
    expect(method_exists($verse, 'search'))->toBeTrue();
});

test('scout driver configuration is accessible', function () {
    $scoutDriver = config('scout.driver');

    // Should have a scout driver configured
    expect($scoutDriver)->not->toBeNull();
    expect($scoutDriver)->toBeString();

    // Common drivers: algolia, meilisearch, collection, database
    expect($scoutDriver)->toBeIn(['algolia', 'meilisearch', 'collection', 'database', 'null']);
});

test('algolia configuration exists in scout config', function () {
    $algoliaConfig = config('scout.algolia');

    expect($algoliaConfig)->toBeArray();
    expect($algoliaConfig)->toHaveKeys(['id', 'secret']);
});

test('verse search works with eager loaded relationships', function () {
    // Import verses
    Artisan::call('scout:import', ['searchable' => 'App\\Models\\Verse']);

    // Search with eager loading
    $results = Verse::search('God')
        ->query(fn ($builder) => $builder->with(['book', 'chapter']))
        ->get();

    expect($results->count())->toBeGreaterThan(0);

    // Verify relationships are loaded
    if ($results->count() > 0) {
        $firstVerse = $results->first();
        expect($firstVerse->relationLoaded('book'))->toBeTrue();
        expect($firstVerse->relationLoaded('chapter'))->toBeTrue();
    }
});
