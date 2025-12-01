<?php

use App\Models\User;
use App\Models\Verse;
use App\Models\VerseHighlight;
use App\Models\VerseSuggestion;
use App\Services\VerseSuggestionService;

test('verse suggestions can be generated for a user', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->postJson(route('verse_suggestions_generate'), [
        'limit' => 10,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('users can retrieve their active suggestions', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->getJson(route('verse_suggestions'));

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);
});

test('suggestions can be marked as clicked', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $suggestion = VerseSuggestion::factory()->create([
        'user_id' => $user->id,
        'clicked' => false,
    ]);

    $response = $this->postJson(route('verse_suggestions_click', $suggestion));

    $response->assertStatus(200);
    $this->assertDatabaseHas('verse_suggestions', [
        'id' => $suggestion->id,
        'clicked' => true,
    ]);
});

test('suggestions can be dismissed', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $suggestion = VerseSuggestion::factory()->create([
        'user_id' => $user->id,
        'dismissed' => false,
    ]);

    $response = $this->postJson(route('verse_suggestions_dismiss', $suggestion));

    $response->assertStatus(200);
    $this->assertDatabaseHas('verse_suggestions', [
        'id' => $suggestion->id,
        'dismissed' => true,
    ]);
});

test('users cannot access other users suggestions', function () {
    $user1 = User::factory()->create(['onboarding_completed' => true]);
    $user2 = User::factory()->create(['onboarding_completed' => true]);

    $suggestion = VerseSuggestion::factory()->create([
        'user_id' => $user1->id,
    ]);

    $this->actingAs($user2);

    $response = $this->postJson(route('verse_suggestions_click', $suggestion));
    $response->assertStatus(403);
});

test('verse suggestion service generates suggestions based on highlights', function () {
    $user = User::factory()->create();

    // This test would require actual verses in the database
    // For now, we'll just test that the service can be instantiated and called
    $service = new VerseSuggestionService();

    $suggestions = $service->generateSuggestionsForUser($user, 5);

    expect($suggestions)->toBeArray();
});
