<?php

use App\Models\Topic;
use App\Models\User;
use App\Models\Verse;

test('authenticated users can view topics index', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->get(route('topics'));
    $response->assertStatus(200);
});

test('guests cannot view topics', function () {
    $response = $this->get(route('topics'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can view a specific topic', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $topic = Topic::factory()->create([
        'title' => 'Test Topic',
        'description' => 'Test Description',
        'is_active' => true,
    ]);

    $response = $this->get(route('topics_show', $topic));
    $response->assertStatus(200);
});

test('topics can be created via API', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->postJson(route('topics_store'), [
        'title' => 'New Topic',
        'description' => 'New Description',
        'category' => 'Faith',
        'order' => 1,
        'is_active' => true,
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
        ]);

    $this->assertDatabaseHas('topics', [
        'title' => 'New Topic',
        'category' => 'Faith',
    ]);
});

test('topics can be updated', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $topic = Topic::factory()->create([
        'title' => 'Original Title',
    ]);

    $response = $this->putJson(route('topics_update', $topic), [
        'title' => 'Updated Title',
    ]);

    $response->assertStatus(200);
    $this->assertDatabaseHas('topics', [
        'id' => $topic->id,
        'title' => 'Updated Title',
    ]);
});

test('topics can be deleted', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $topic = Topic::factory()->create();

    $response = $this->deleteJson(route('topics_destroy', $topic));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('topics', [
        'id' => $topic->id,
    ]);
});
