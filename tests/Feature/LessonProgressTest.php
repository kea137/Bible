<?php

use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Create a user with role
    $userRole = Role::factory()->create(['role_number' => 3, 'name' => 'User']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($userRole);

    // Create a test lesson
    $this->lesson = Lesson::factory()->create([
        'title' => 'Test Lesson',
        'description' => 'This is a test lesson',
        'language' => 'English',
        'readable' => true,
        'user_id' => $this->user->id,
        'no_paragraphs' => 1,
    ]);

    // Create a paragraph for the lesson
    $this->lesson->paragraphs()->create([
        'text' => 'This is a test paragraph.',
    ]);
});

test('authenticated user can access lesson progress toggle endpoint', function () {
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ])
        ->assertOk();
});

test('unauthenticated user cannot access lesson progress toggle endpoint', function () {
    $this->postJson('/api/lesson/progress', [
        'lesson_id' => $this->lesson->id,
    ])
        ->assertUnauthorized();
});

test('user can mark lesson as read', function () {
    $response = actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ]);

    $response->assertOk()
        ->assertJsonStructure([
            'success',
            'progress' => [
                'id',
                'user_id',
                'lesson_id',
                'completed',
                'completed_at',
            ],
        ]);

    // Verify the progress was created in the database
    $progress = LessonProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->first();

    expect($progress)->not->toBeNull();
    expect($progress->completed)->toBeTrue();
    expect($progress->completed_at)->not->toBeNull();
});

test('user can toggle lesson completion status', function () {
    // First mark as completed
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ])
        ->assertOk();

    $progress = LessonProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->first();

    expect($progress->completed)->toBeTrue();

    // Now toggle it back to incomplete
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ])
        ->assertOk();

    $progress->refresh();
    expect($progress->completed)->toBeFalse();
    expect($progress->completed_at)->toBeNull();
});

test('multiple users can track lesson progress independently', function () {
    $userRole2 = Role::factory()->create(['role_number' => 4, 'name' => 'User2']);
    $user2 = User::factory()->create();
    $user2->roles()->attach($userRole2);

    // First user marks as complete
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ])
        ->assertOk();

    // Second user marks as complete
    actingAs($user2)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => $this->lesson->id,
        ])
        ->assertOk();

    // Verify both progress records exist
    $progress1 = LessonProgress::where('user_id', $this->user->id)
        ->where('lesson_id', $this->lesson->id)
        ->first();

    $progress2 = LessonProgress::where('user_id', $user2->id)
        ->where('lesson_id', $this->lesson->id)
        ->first();

    expect($progress1)->not->toBeNull();
    expect($progress2)->not->toBeNull();
    expect($progress1->id)->not->toBe($progress2->id);
});

test('lesson progress requires valid lesson id', function () {
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [
            'lesson_id' => 99999,
        ])
        ->assertJsonValidationErrors(['lesson_id']);
});

test('lesson progress endpoint validates lesson_id is required', function () {
    actingAs($this->user)
        ->postJson('/api/lesson/progress', [])
        ->assertJsonValidationErrors(['lesson_id']);
});
