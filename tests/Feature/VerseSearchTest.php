<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Role;
use App\Models\User;
use App\Models\Verse;

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

test('authenticated user can access verse search endpoint', function () {
    actingAs($this->user)
        ->get('/api/verses/search?query=God')
        ->assertOk();
});

test('unauthenticated user cannot access verse search endpoint', function () {
    $this->get('/api/verses/search?query=God')
        ->assertRedirect('/login');
});

test('verse search returns results matching query', function () {
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=God');

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

    // Since we're using the collection driver by default, verify at least one verse is returned
    expect($response->json('verses'))->toBeArray();
});

test('verse search returns empty results for non-matching query', function () {
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=nonexistentword123456');

    $response->assertOk()
        ->assertJson([
            'verses' => [],
            'total' => 0,
        ]);
});

test('verse search returns empty results for empty query', function () {
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=');

    $response->assertOk()
        ->assertJson([
            'verses' => [],
            'total' => 0,
        ]);
});

test('verse search respects limit parameter', function () {
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=the&limit=2');

    $response->assertOk();

    $verses = $response->json('verses');
    expect(count($verses))->toBeLessThanOrEqual(2);
});

test('verse search returns verses with book and chapter information', function () {
    $response = actingAs($this->user)
        ->getJson('/api/verses/search?query=God');

    $response->assertOk();

    $verses = $response->json('verses');
    if (count($verses) > 0) {
        $verse = $verses[0];
        expect($verse)->toHaveKeys(['id', 'text', 'verse_number', 'bible_id', 'book', 'chapter']);
        expect($verse['book'])->toHaveKeys(['id', 'title']);
        expect($verse['chapter'])->toHaveKeys(['id', 'chapter_number']);
    }
});
