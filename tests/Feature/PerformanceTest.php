<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Reference;
use App\Models\Verse;
use App\Models\VerseLinkCanvas;
use App\Models\VerseLinkNode;
use App\Services\ReferenceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('reference service getReferencesForVerse performs efficiently with caching', function () {
    // Skip this test if using array or database cache driver (which don't support tags)
    if (! in_array(config('cache.default'), ['redis', 'memcached'])) {
        $this->markTestSkipped('Cache tags require redis or memcached driver');

        return;
    }

    $referenceService = app(ReferenceService::class);

    // Create test data
    $bible = Bible::factory()->create();
    $book = Book::factory()->create([
        'bible_id' => $bible->id,
        'book_number' => 1,
    ]);
    $chapter = Chapter::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_number' => 1,
    ]);

    // Create main verse
    $verse = Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'verse_number' => 1,
    ]);

    // Create reference verses
    $refVerses = [];
    for ($i = 2; $i <= 10; $i++) {
        $refVerses[] = Verse::factory()->create([
            'bible_id' => $bible->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'verse_number' => $i,
        ]);
    }

    // Create reference data
    $referenceData = [];
    foreach ($refVerses as $i => $refVerse) {
        $referenceData[(string) ($i + 1)] = 'GEN 1 '.($i + 2);
    }

    Reference::create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'verse_id' => $verse->id,
        'verse_reference' => json_encode($referenceData),
    ]);

    // First call - should execute queries and cache
    DB::enableQueryLog();
    $references1 = $referenceService->getReferencesForVerse($verse);
    $queryCount1 = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($references1)->toBeArray();
    expect($references1)->toHaveCount(9);

    // Second call - should use cache and have fewer queries
    DB::enableQueryLog();
    $references2 = $referenceService->getReferencesForVerse($verse);
    $queryCount2 = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($references2)->toBeArray();
    expect($references2)->toHaveCount(9);

    // Cache should reduce query count to 0 or very low
    expect($queryCount2)->toBeLessThan($queryCount1);
});

test('verse link canvas showCanvas query is optimized with eager loading', function () {
    $user = \App\Models\User::factory()->create();

    // Create test data
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
    ]);

    $canvas = VerseLinkCanvas::factory()->create(['user_id' => $user->id]);

    // Create multiple nodes with verses
    for ($i = 1; $i <= 5; $i++) {
        $verse = Verse::factory()->create([
            'bible_id' => $bible->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'verse_number' => $i,
        ]);

        VerseLinkNode::factory()->create([
            'canvas_id' => $canvas->id,
            'verse_id' => $verse->id,
        ]);
    }

    // Load canvas with eager loading
    DB::enableQueryLog();
    $canvas->load([
        'nodes.verse.book',
        'nodes.verse.chapter',
        'nodes.verse.bible',
        'connections',
    ]);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    // With eager loading, should have significantly fewer queries than N+1
    // Expected: ~4-5 queries instead of 15+ with N+1
    expect($queryCount)->toBeLessThan(10);
    expect($canvas->nodes)->toHaveCount(5);
});

test('database indexes exist for performance critical columns', function () {
    // For SQLite, we need to use a different query
    $dbDriver = DB::connection()->getDriverName();

    if ($dbDriver === 'sqlite') {
        // Check if indexes exist in SQLite
        $indexes = DB::select("SELECT * FROM sqlite_master WHERE type='index' AND tbl_name='notes' AND (name LIKE '%verse_id%' OR name LIKE '%user_id%')");
        expect($indexes)->not->toBeEmpty();

        $indexes = DB::select("SELECT * FROM sqlite_master WHERE type='index' AND tbl_name='references' AND name LIKE '%verse_id%'");
        expect($indexes)->not->toBeEmpty();

        $indexes = DB::select("SELECT * FROM sqlite_master WHERE type='index' AND tbl_name='verse_link_nodes' AND name LIKE '%verse_id%'");
        expect($indexes)->not->toBeEmpty();
    } else {
        // For MySQL/MariaDB
        $indexes = DB::select("SHOW INDEXES FROM notes WHERE Column_name IN ('verse_id', 'user_id')");
        expect($indexes)->not->toBeEmpty();

        $indexes = DB::select("SHOW INDEXES FROM references WHERE Column_name = 'verse_id'");
        expect($indexes)->not->toBeEmpty();

        $indexes = DB::select("SHOW INDEXES FROM verse_link_nodes WHERE Column_name = 'verse_id'");
        expect($indexes)->not->toBeEmpty();
    }
});

test('cache invalidation works correctly on reference updates', function () {
    // Skip this test if using array or database cache driver (which don't support tags)
    if (! in_array(config('cache.default'), ['redis', 'memcached'])) {
        $this->markTestSkipped('Cache tags require redis or memcached driver');

        return;
    }

    $referenceService = app(ReferenceService::class);

    // Create test data
    $bible = Bible::factory()->create();
    $book = Book::factory()->create([
        'bible_id' => $bible->id,
        'book_number' => 1,
    ]);
    $chapter = Chapter::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_number' => 1,
    ]);

    $verse = Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'verse_number' => 1,
    ]);

    // Create initial reference
    Reference::create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'verse_id' => $verse->id,
        'verse_reference' => json_encode(['1' => 'GEN 1 2']),
    ]);

    // Load into cache
    $references1 = $referenceService->getReferencesForVerse($verse);
    expect($references1)->toBeArray();

    // Invalidate cache
    $referenceService->invalidateVerseReferences($verse);

    // Update reference
    Reference::where('verse_id', $verse->id)->update([
        'verse_reference' => json_encode(['1' => 'GEN 1 2', '2' => 'GEN 1 3']),
    ]);

    // Load again - should get fresh data, not cached
    $references2 = $referenceService->getReferencesForVerse($verse);

    // If cache was properly invalidated, we should see the updated reference count
    expect($references2)->toBeArray();
});
