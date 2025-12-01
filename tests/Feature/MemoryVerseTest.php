<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\MemoryVerse;
use App\Models\User;
use App\Models\Verse;
use App\Services\SpacedRepetitionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create test user
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'onboarding_completed' => true,
    ]);

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
        'name' => 'John',
        'title' => 'John',
    ]);

    $this->chapter = Chapter::create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_number' => 3,
    ]);

    $this->verse = Verse::create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_number' => 16,
        'text' => 'For God so loved the world, that he gave his only begotten Son, that whosoever believeth in him should not perish, but have everlasting life.',
    ]);
});

test('authenticated user can mark a verse as memory verse', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/mobile/memory-verses', [
            'verse_id' => $this->verse->id,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'message',
            'memory_verse' => [
                'id',
                'verse_id',
                'verse_text',
                'book_name',
                'chapter_number',
                'verse_number',
                'next_review_date',
            ],
        ]);

    $this->assertDatabaseHas('memory_verses', [
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
    ]);
});

test('cannot mark the same verse as memory verse twice', function () {
    // First time should succeed
    $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/mobile/memory-verses', [
            'verse_id' => $this->verse->id,
        ])
        ->assertStatus(201);

    // Second time should fail
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/mobile/memory-verses', [
            'verse_id' => $this->verse->id,
        ]);

    $response->assertStatus(409)
        ->assertJson([
            'message' => 'This verse is already marked as a memory verse',
        ]);
});

test('can retrieve all memory verses', function () {
    MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 1,
        'next_review_date' => now()->addDay(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/mobile/memory-verses');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'memory_verses' => [
                '*' => [
                    'id',
                    'verse_id',
                    'verse_text',
                    'book_name',
                    'chapter_number',
                    'verse_number',
                    'next_review_date',
                    'is_due',
                    'success_rate',
                ],
            ],
        ]);

    expect($response->json('memory_verses'))->toHaveCount(1);
});

test('can retrieve due memory verses', function () {
    // Create a due memory verse
    MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 1,
        'next_review_date' => now()->subDay(), // Due yesterday
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/mobile/memory-verses/due');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'due_verses',
            'count',
        ]);

    expect($response->json('count'))->toBe(1);
});

test('can submit a review for a memory verse', function () {
    $memoryVerse = MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 1,
        'next_review_date' => now(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/mobile/memory-verses/{$memoryVerse->id}/review", [
            'quality' => 4, // Good recall
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'message',
            'memory_verse' => [
                'id',
                'verse_id',
                'next_review_date',
                'interval',
                'repetitions',
                'success_rate',
            ],
        ]);

    // Verify the database was updated
    $memoryVerse->refresh();
    expect($memoryVerse->total_reviews)->toBe(1);
    expect($memoryVerse->correct_reviews)->toBe(1);
    expect($memoryVerse->repetitions)->toBe(1);
});

test('can get memory verse statistics', function () {
    MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 2,
        'easiness_factor' => 2.5,
        'interval' => 6,
        'next_review_date' => now()->addDays(6),
        'total_reviews' => 5,
        'correct_reviews' => 4,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/mobile/memory-verses/statistics');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'total_memory_verses',
            'due_for_review',
            'total_reviews',
            'correct_reviews',
            'overall_success_rate',
        ]);

    expect($response->json('total_memory_verses'))->toBe(1);
    expect($response->json('total_reviews'))->toBe(5);
    expect($response->json('correct_reviews'))->toBe(4);
    expect($response->json('overall_success_rate'))->toBe(80);
});

test('can delete a memory verse', function () {
    $memoryVerse = MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 1,
        'next_review_date' => now()->addDay(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    $response = $this->actingAs($this->user, 'sanctum')
        ->deleteJson("/api/mobile/memory-verses/{$memoryVerse->id}");

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Memory verse removed successfully',
        ]);

    $this->assertDatabaseMissing('memory_verses', [
        'id' => $memoryVerse->id,
    ]);
});

test('SM-2 algorithm updates intervals correctly for correct responses', function () {
    $service = new SpacedRepetitionService();
    $memoryVerse = MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 0,
        'next_review_date' => now(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    // First correct review (quality = 4)
    $service->updateReviewSchedule($memoryVerse, 4);
    $memoryVerse->refresh();
    expect($memoryVerse->repetitions)->toBe(1);
    expect($memoryVerse->interval)->toBe(1);

    // Second correct review
    $service->updateReviewSchedule($memoryVerse, 4);
    $memoryVerse->refresh();
    expect($memoryVerse->repetitions)->toBe(2);
    expect($memoryVerse->interval)->toBe(6);

    // Third correct review
    $service->updateReviewSchedule($memoryVerse, 4);
    $memoryVerse->refresh();
    expect($memoryVerse->repetitions)->toBe(3);
    expect($memoryVerse->interval)->toBeGreaterThan(6);
});

test('SM-2 algorithm resets on incorrect response', function () {
    $service = new SpacedRepetitionService();
    $memoryVerse = MemoryVerse::create([
        'user_id' => $this->user->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 3,
        'easiness_factor' => 2.5,
        'interval' => 15,
        'next_review_date' => now(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    // Incorrect review (quality = 2)
    $service->updateReviewSchedule($memoryVerse, 2);
    $memoryVerse->refresh();
    
    expect($memoryVerse->repetitions)->toBe(0);
    expect($memoryVerse->interval)->toBe(1);
});

test('unauthenticated users cannot access memory verse endpoints', function () {
    $this->getJson('/api/mobile/memory-verses')
        ->assertStatus(401);

    $this->postJson('/api/mobile/memory-verses', ['verse_id' => $this->verse->id])
        ->assertStatus(401);

    $this->getJson('/api/mobile/memory-verses/due')
        ->assertStatus(401);

    $this->getJson('/api/mobile/memory-verses/statistics')
        ->assertStatus(401);
});

test('users can only access their own memory verses', function () {
    $otherUser = User::factory()->create();
    
    $memoryVerse = MemoryVerse::create([
        'user_id' => $otherUser->id,
        'verse_id' => $this->verse->id,
        'repetitions' => 0,
        'easiness_factor' => 2.5,
        'interval' => 1,
        'next_review_date' => now()->addDay(),
        'total_reviews' => 0,
        'correct_reviews' => 0,
    ]);

    // Try to review another user's memory verse
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson("/api/mobile/memory-verses/{$memoryVerse->id}/review", [
            'quality' => 4,
        ]);

    $response->assertStatus(404);
});
