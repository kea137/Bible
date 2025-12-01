<?php

use App\Models\ActivityLog;
use App\Models\Lesson;
use App\Models\Note;
use App\Models\User;
use App\Models\VerseHighlight;

test('authenticated user can export their data', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('data.export'));

    $response->assertStatus(200);
    $response->assertDownload();
});

test('data export includes user information', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    $this
        ->actingAs($user)
        ->get(route('data.export'));

    // Verify activity log was created
    $log = ActivityLog::where('action', 'data_export')
        ->where('subject_user_id', $user->id)
        ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($user->id);
    expect($log->description)->toContain('Test User');
});

test('data export creates zip file with json', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get(route('data.export'));

    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/zip');
});

test('unauthenticated user cannot export data', function () {
    $response = $this->get(route('data.export'));

    $response->assertRedirect(route('login'));
});
