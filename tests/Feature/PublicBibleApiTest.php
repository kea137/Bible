<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test Bible data
    $this->bible = Bible::create([
        'name' => 'King James Version',
        'abbreviation' => 'KJV',
        'language' => 'English',
        'version' => 'King James Version',
        'description' => 'Test Bible',
    ]);

    $this->book = Book::create([
        'bible_id' => $this->bible->id,
        'book_number' => 1,
        'title' => 'Genesis',
    ]);

    $this->chapter = Chapter::create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_number' => 1,
    ]);

    Verse::create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 1,
        'text' => 'In the beginning God created the heaven and the earth.',
    ]);

    Verse::create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 2,
        'text' => 'And the earth was without form, and void; and darkness was upon the face of the deep.',
    ]);
});

test('public api can fetch verses without authentication', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=Genesis&chapter=1');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'bible' => ['name', 'abbreviation', 'language', 'version'],
            'book' => ['name', 'number'],
            'chapter' => ['number'],
            'verses',
            'count',
        ]);

    expect($response->json('count'))->toBe(2);
    expect($response->json('verses')[0]['number'])->toBe(1);
    expect($response->json('verses')[0]['text'])->toContain('In the beginning');
});

test('public api can fetch specific verse', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=Genesis&chapter=1&verse=1');

    $response->assertStatus(200);
    expect($response->json('count'))->toBe(1);
    expect($response->json('verses')[0]['number'])->toBe(1);
});

test('public api can filter by language', function () {
    $response = $this->getJson('/api/verses?language=English&book=Genesis&chapter=1');

    $response->assertStatus(200);
    expect($response->json('bible')['language'])->toBe('English');
});

test('public api returns error for non-existent Bible version', function () {
    $response = $this->getJson('/api/verses?version=NONEXISTENT&book=Genesis&chapter=1');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'No Bible found matching the specified criteria',
        ]);
});

test('public api returns error for non-existent book', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=NonExistentBook&chapter=1');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Book not found',
        ]);
});

test('public api returns error for non-existent chapter', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=Genesis&chapter=999');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Chapter not found',
        ]);
});

test('public api requires book parameter', function () {
    $response = $this->getJson('/api/verses?version=KJV&chapter=1');

    $response->assertStatus(422);
});

test('public api requires chapter parameter', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=Genesis');

    $response->assertStatus(422);
});

test('public api can find book by number', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=1&chapter=1');

    $response->assertStatus(200);
    expect($response->json('book')['name'])->toBe('Genesis');
});

test('public api supports partial book name matching', function () {
    $response = $this->getJson('/api/verses?version=KJV&book=Gen&chapter=1');

    $response->assertStatus(200);
    expect($response->json('book')['name'])->toBe('Genesis');
});

test('public api is throttled', function () {
    // Make 61 requests (one more than the limit of 60 per minute)
    for ($i = 0; $i < 61; $i++) {
        $response = $this->getJson('/api/verses?version=KJV&book=Genesis&chapter=1');
        
        if ($i < 60) {
            $response->assertStatus(200);
        } else {
            // The 61st request should be throttled
            $response->assertStatus(429);
        }
    }
});
