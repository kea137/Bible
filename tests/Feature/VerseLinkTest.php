<?php

use App\Models\User;
use App\Models\VerseLinkCanvas;

test('guests are redirected to login from verse link page', function () {
    $response = $this->get(route('verse_link'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the verse link page', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->get(route('verse_link'));
    $response->assertStatus(200);
});

test('authenticated users can create a canvas', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $this->actingAs($user);

    $response = $this->postJson(route('verse_link_store_canvas'), [
        'name' => 'Test Canvas',
        'description' => 'A test canvas for verse linking',
    ]);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'message' => 'Canvas created successfully',
    ]);

    $this->assertDatabaseHas('verse_link_canvases', [
        'name' => 'Test Canvas',
        'user_id' => $user->id,
    ]);
});

test('authenticated users can update their canvas', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user->id,
        'name' => 'Original Name',
        'description' => 'Original description',
    ]);

    $this->actingAs($user);

    $response = $this->putJson(route('verse_link_update_canvas', $canvas), [
        'name' => 'Updated Name',
        'description' => 'Updated description',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $this->assertDatabaseHas('verse_link_canvases', [
        'id' => $canvas->id,
        'name' => 'Updated Name',
    ]);
});

test('users cannot update other users canvas', function () {
    $user1 = User::factory()->create(['onboarding_completed' => true]);
    $user2 = User::factory()->create(['onboarding_completed' => true]);

    $canvas = VerseLinkCanvas::create([
        'user_id' => $user1->id,
        'name' => 'User 1 Canvas',
    ]);

    $this->actingAs($user2);

    $response = $this->putJson(route('verse_link_update_canvas', $canvas), [
        'name' => 'Hacked Name',
    ]);

    $response->assertStatus(403);
});

test('authenticated users can delete their canvas', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user->id,
        'name' => 'To Be Deleted',
    ]);

    $this->actingAs($user);

    $response = $this->deleteJson(route('verse_link_destroy_canvas', $canvas));

    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
    ]);

    $this->assertDatabaseMissing('verse_link_canvases', [
        'id' => $canvas->id,
    ]);
});
