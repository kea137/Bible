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
    $response = $this->getJson('/api/English/KJV/false/Genesis/1');

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
    $response = $this->getJson('/api/English/KJV/false/Genesis/1/1');

    $response->assertStatus(200);
    expect($response->json('count'))->toBe(1);
    expect($response->json('verses')[0]['number'])->toBe(1);
});

test('public api can filter by language', function () {
    $response = $this->getJson('/api/English/KJV/false/Genesis/1');

    $response->assertStatus(200);
    expect($response->json('bible')['language'])->toBe('English');
});

test('public api returns error for non-existent Bible version', function () {
    $response = $this->getJson('/api/English/NONEXISTENT/false/Genesis/1');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'No Bible found matching the specified criteria',
        ]);
});

test('public api returns error for non-existent book', function () {
    $response = $this->getJson('/api/English/KJV/false/NonExistentBook/1');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Book not found',
        ]);
});

test('public api returns error for non-existent chapter', function () {
    $response = $this->getJson('/api/English/KJV/false/Genesis/999');

    $response->assertStatus(404)
        ->assertJson([
            'error' => 'Chapter not found',
        ]);
});

test('public api can find book by number', function () {
    $response = $this->getJson('/api/English/KJV/false/1/1');

    $response->assertStatus(200);
    expect($response->json('book')['name'])->toBe('Genesis');
});

test('public api supports partial book name matching', function () {
    $response = $this->getJson('/api/English/KJV/false/Gen/1');

    $response->assertStatus(200);
    expect($response->json('book')['name'])->toBe('Genesis');
});

test('public api can include references', function () {
    $response = $this->getJson('/api/English/KJV/true/Genesis/1/1');

    $response->assertStatus(200);
    expect($response->json('verses')[0])->toHaveKey('text');
    // References key will only exist if there are actual references in the database
});

test('public api handles references parameter as boolean string', function () {
    $response1 = $this->getJson('/api/English/KJV/true/Genesis/1/1');
    $response2 = $this->getJson('/api/English/KJV/false/Genesis/1/1');

    $response1->assertStatus(200);
    $response2->assertStatus(200);
});

test('public api is throttled to 30 requests per minute', function () {
    // Make 31 requests (one more than the limit of 30 per minute)
    for ($i = 0; $i < 31; $i++) {
        $response = $this->getJson('/api/English/KJV/false/Genesis/1');
        
        if ($i < 30) {
            $response->assertStatus(200);
        } else {
            // The 31st request should be throttled
            $response->assertStatus(429);
        }
    }
});
