<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Note;
use App\Models\ReadingProgress;
use App\Models\User;
use App\Models\Verse;
use App\Models\VerseHighlight;

test('home endpoint returns success', function () {
    $response = $this->get('/api/mobile/home');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Welcome to Bible API',
        ]);
});

test('dashboard requires authentication', function () {
    $response = $this->getJson('/api/mobile/dashboard');

    $response->assertStatus(401);
});

test('authenticated users can access dashboard', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/dashboard');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'verseOfTheDay',
                'lastReading',
                'readingStats',
                'userName',
            ],
        ]);
});

test('onboarding endpoint returns bible data', function () {
    $user = User::factory()->create();
    Bible::factory()->create(['name' => 'KJV', 'language' => 'English']);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/onboarding');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'bibles',
                'currentLanguage',
                'onboarding_completed',
            ],
        ]);
});

test('can update user locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/update-locale', [
            'locale' => 'es',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'locale' => 'es',
        ]);

    expect($user->fresh()->language)->toBe('es');
});

test('can update user theme', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/update-theme', [
            'theme' => 'dark',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'theme' => 'dark',
        ]);

    expect($user->fresh()->appearance_preferences['theme'])->toBe('dark');
});

test('can update preferred translations', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/update-translations', [
            'preferred_translations' => [$bible->id],
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'translations' => [$bible->id],
        ]);

    expect($user->fresh()->preferred_translations)->toBe([$bible->id]);
});

test('can get list of bibles', function () {
    $user = User::factory()->create();
    Bible::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/bibles');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'abbreviation', 'language', 'version'],
            ],
        ]);
});

test('can get specific bible', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id, 'bible_id' => $bible->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/mobile/bibles/{$bible->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'bible',
                'initialChapter',
            ],
        ]);
});

test('can get chapter with verses', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id, 'bible_id' => $bible->id]);
    Verse::factory()->count(5)->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson("/api/mobile/bibles/chapters/{$chapter->id}");

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'id',
                'verses',
                'book',
            ],
        ]);
});

test('can store verse highlight', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/verse-highlights', [
            'verse_id' => $verse->id,
            'color' => 'yellow',
            'note' => 'Important verse',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect(VerseHighlight::where('user_id', $user->id)->where('verse_id', $verse->id)->exists())->toBeTrue();
});

test('can delete verse highlight', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    VerseHighlight::create([
        'user_id' => $user->id,
        'verse_id' => $verse->id,
        'color' => 'yellow',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/mobile/verse-highlights/{$verse->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect(VerseHighlight::where('user_id', $user->id)->where('verse_id', $verse->id)->exists())->toBeFalse();
});

test('can get all verse highlights', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    VerseHighlight::create([
        'user_id' => $user->id,
        'verse_id' => $verse->id,
        'color' => 'yellow',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/verse-highlights');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
        ]);
});

test('can create note', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/notes', [
            'verse_id' => $verse->id,
            'title' => 'My Note',
            'content' => 'This is my note content',
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'success' => true,
            'message' => 'Note saved successfully',
        ]);

    expect(Note::where('user_id', $user->id)->where('verse_id', $verse->id)->exists())->toBeTrue();
});

test('can update note', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $note = Note::create([
        'user_id' => $user->id,
        'verse_id' => $verse->id,
        'title' => 'Original Title',
        'content' => 'Original Content',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->putJson("/api/mobile/notes/{$note->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated Content',
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Note updated successfully',
        ]);

    expect($note->fresh()->title)->toBe('Updated Title');
});

test('can delete note', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $note = Note::create([
        'user_id' => $user->id,
        'verse_id' => $verse->id,
        'title' => 'My Note',
        'content' => 'My Content',
    ]);

    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson("/api/mobile/notes/{$note->id}");

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect(Note::find($note->id))->toBeNull();
});

test('cannot access other users notes', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id]);
    $verse = Verse::factory()->create(['chapter_id' => $chapter->id, 'book_id' => $book->id, 'bible_id' => $bible->id]);

    $note = Note::create([
        'user_id' => $user1->id,
        'verse_id' => $verse->id,
        'title' => 'User 1 Note',
        'content' => 'User 1 Content',
    ]);

    $response = $this->actingAs($user2, 'sanctum')
        ->getJson("/api/mobile/notes/{$note->id}");

    $response->assertStatus(403);
});

test('can toggle reading progress', function () {
    $user = User::factory()->create();
    $bible = Bible::factory()->create();
    $book = Book::factory()->create(['bible_id' => $bible->id]);
    $chapter = Chapter::factory()->create(['book_id' => $book->id, 'bible_id' => $bible->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/reading-progress/toggle', [
            'chapter_id' => $chapter->id,
            'bible_id' => $bible->id,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect(ReadingProgress::where('user_id', $user->id)->where('chapter_id', $chapter->id)->exists())->toBeTrue();
});

test('can get reading statistics', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/reading-progress/statistics');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'total_chapters_completed',
                'chapters_read_today',
                'verses_read_today',
                'last_reading',
            ],
        ]);
});

test('can get lessons', function () {
    $user = User::factory()->create();
    Lesson::factory()->count(3)->create(['readable' => true]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/lessons');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
        ]);
});

test('can toggle lesson progress', function () {
    $user = User::factory()->create();
    $lesson = Lesson::factory()->create(['readable' => true]);

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/mobile/lesson-progress/toggle', [
            'lesson_id' => $lesson->id,
        ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    expect(LessonProgress::where('user_id', $user->id)->where('lesson_id', $lesson->id)->exists())->toBeTrue();
});

test('sitemap returns bible data', function () {
    $user = User::factory()->create();
    Bible::factory()->count(3)->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/sitemap');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data' => [
                'bibles',
            ],
        ]);
});

test('parallel bibles returns user preferred translations', function () {
    $user = User::factory()->create();
    $bible1 = Bible::factory()->create();
    $bible2 = Bible::factory()->create();

    $user->update(['preferred_translations' => [$bible1->id, $bible2->id]]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/mobile/bibles/parallel');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'data',
        ]);
});
