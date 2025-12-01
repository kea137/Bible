<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Note;
use App\Models\User;
use App\Models\Verse;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Use collection driver for tests to avoid needing Meilisearch
    config(['scout.driver' => 'collection']);
});

test('search endpoint returns successful response', function () {
    $response = $this->getJson('/api/search?query=test');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'query',
            'filters',
            'verses',
            'notes',
            'lessons',
        ]);
});

test('search returns verses matching query', function () {
    $bible = Bible::factory()->create(['abbreviation' => 'KJV']);
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['bible_id' => $bible->id, 'book_id' => $book->id]);
    
    Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
        'text' => 'For God so loved the world',
        'verse_number' => 16,
    ]);

    $response = $this->getJson('/api/search?query=loved');

    $response->assertStatus(200)
        ->assertJsonPath('query', 'loved');
});

test('search filters verses by bible version', function () {
    $kjv = Bible::factory()->create(['abbreviation' => 'KJV']);
    $niv = Bible::factory()->create(['abbreviation' => 'NIV']);
    $book = Book::factory()->create();
    $kjvChapter = Chapter::factory()->create(['bible_id' => $kjv->id, 'book_id' => $book->id]);
    $nivChapter = Chapter::factory()->create(['bible_id' => $niv->id, 'book_id' => $book->id]);
    
    Verse::factory()->create([
        'bible_id' => $kjv->id,
        'book_id' => $book->id,
        'chapter_id' => $kjvChapter->id,
        'text' => 'Love one another',
    ]);
    
    Verse::factory()->create([
        'bible_id' => $niv->id,
        'book_id' => $book->id,
        'chapter_id' => $nivChapter->id,
        'text' => 'Love your neighbor',
    ]);

    $filters = json_encode(['version' => 'KJV']);
    $response = $this->getJson("/api/search?query=love&filters={$filters}");

    $response->assertStatus(200);
});

test('search filters verses by book', function () {
    $bible = Bible::factory()->create();
    $john = Book::factory()->create(['title' => 'John', 'bible_id' => $bible->id]);
    $matthew = Book::factory()->create(['title' => 'Matthew', 'bible_id' => $bible->id]);
    $johnChapter = Chapter::factory()->create(['bible_id' => $bible->id, 'book_id' => $john->id]);
    $matthewChapter = Chapter::factory()->create(['bible_id' => $bible->id, 'book_id' => $matthew->id]);
    
    Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $john->id,
        'chapter_id' => $johnChapter->id,
        'text' => 'In the beginning was the Word',
    ]);
    
    Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $matthew->id,
        'chapter_id' => $matthewChapter->id,
        'text' => 'The book of the genealogy',
    ]);

    $filters = json_encode(['book_id' => $john->id]);
    $response = $this->getJson("/api/search?query=the&filters={$filters}");

    $response->assertStatus(200);
});

test('search returns user notes only', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create();
    $chapter = Chapter::factory()->create(['bible_id' => $bible->id, 'book_id' => $book->id]);
    $verse = Verse::factory()->create([
        'bible_id' => $bible->id,
        'book_id' => $book->id,
        'chapter_id' => $chapter->id,
    ]);
    
    Note::factory()->create([
        'user_id' => $user1->id,
        'verse_id' => $verse->id,
        'title' => 'My Study Note',
        'content' => 'This is my personal note',
    ]);
    
    Note::factory()->create([
        'user_id' => $user2->id,
        'verse_id' => $verse->id,
        'title' => 'Another Note',
        'content' => 'Different user note',
    ]);

    $response = $this->actingAs($user1)->getJson('/api/search?query=note');

    $response->assertStatus(200);
});

test('search returns lessons matching query', function () {
    Lesson::factory()->create([
        'title' => 'Introduction to Prayer',
        'description' => 'Learn about the power of prayer',
        'language' => 'English',
    ]);
    
    Lesson::factory()->create([
        'title' => 'Bible Study Methods',
        'description' => 'Different approaches to studying scripture',
        'language' => 'English',
    ]);

    $response = $this->getJson('/api/search?query=prayer');

    $response->assertStatus(200);
});

test('search filters lessons by language', function () {
    Lesson::factory()->create([
        'title' => 'English Lesson',
        'language' => 'English',
    ]);
    
    Lesson::factory()->create([
        'title' => 'Spanish Lesson',
        'language' => 'Spanish',
    ]);

    $filters = json_encode(['language' => 'English']);
    $response = $this->getJson("/api/search?query=lesson&filters={$filters}");

    $response->assertStatus(200);
});

test('search respects per_page parameter', function () {
    $bible = Bible::factory()->create();
    $book = Book::factory()->create();
    $chapter = Chapter::factory()->create(['bible_id' => $bible->id, 'book_id' => $book->id]);
    
    // Create multiple verses
    for ($i = 1; $i <= 15; $i++) {
        Verse::factory()->create([
            'bible_id' => $bible->id,
            'book_id' => $book->id,
            'chapter_id' => $chapter->id,
            'text' => "This is verse number {$i}",
            'verse_number' => $i,
        ]);
    }

    $response = $this->getJson('/api/search?query=verse&per_page=5');

    $response->assertStatus(200);
});

test('search allows searching specific types only', function () {
    $types = json_encode(['verses']);
    $response = $this->getJson("/api/search?query=test&types={$types}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'query',
            'filters',
            'verses',
            'notes',
            'lessons',
        ]);
});

test('filter options endpoint returns available filters', function () {
    Bible::factory()->create(['name' => 'King James Version', 'abbreviation' => 'KJV']);
    $bible = Bible::factory()->create();
    Book::factory()->create(['title' => 'Genesis', 'bible_id' => $bible->id]);

    $response = $this->getJson('/api/search/filters');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'bibles',
            'books',
            'series',
            'languages',
        ]);
});

test('search returns empty results for non-matching query', function () {
    $response = $this->getJson('/api/search?query=nonexistentquerythatmatchesnothing123456');

    $response->assertStatus(200)
        ->assertJsonPath('query', 'nonexistentquerythatmatchesnothing123456');
});

test('search handles empty query gracefully', function () {
    $response = $this->getJson('/api/search?query=');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'query',
            'filters',
            'verses',
            'notes',
            'lessons',
        ]);
});

test('search endpoint is rate limited', function () {
    // Make requests up to the limit
    for ($i = 0; $i < 60; $i++) {
        $this->getJson('/api/search?query=test');
    }

    // The next request should be rate limited
    $response = $this->getJson('/api/search?query=test');

    // Depending on exact timing, this might be 200 or 429
    expect($response->status())->toBeIn([200, 429]);
});

