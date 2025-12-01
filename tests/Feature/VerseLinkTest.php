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

test('users cannot update other user\'s canvas', function () {
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

// Collaboration tests

test('owner can share canvas with another user as editor', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $editor = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Shared Canvas',
    ]);

    $this->actingAs($owner);

    $response = $this->postJson(route('verse_link_share_canvas', $canvas), [
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $this->assertDatabaseHas('canvas_permissions', [
        'canvas_id' => $canvas->id,
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    // Canvas should be marked as collaborative
    $canvas->refresh();
    expect($canvas->is_collaborative)->toBeTrue();
});

test('owner can share canvas with viewer permission', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $viewer = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Shared Canvas',
    ]);

    $this->actingAs($owner);

    $response = $this->postJson(route('verse_link_share_canvas', $canvas), [
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('canvas_permissions', [
        'canvas_id' => $canvas->id,
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);
});

test('non-owner cannot share canvas', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $user = User::factory()->create(['onboarding_completed' => true]);
    $otherUser = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Owner Canvas',
    ]);

    $this->actingAs($user);

    $response = $this->postJson(route('verse_link_share_canvas', $canvas), [
        'user_id' => $otherUser->id,
        'role' => 'editor',
    ]);

    $response->assertStatus(403);
});

test('editor can update canvas', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $editor = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Original Name',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $this->actingAs($editor);

    $response = $this->putJson(route('verse_link_update_canvas', $canvas), [
        'name' => 'Updated by Editor',
    ]);

    $response->assertStatus(200);
    
    $this->assertDatabaseHas('verse_link_canvases', [
        'id' => $canvas->id,
        'name' => 'Updated by Editor',
    ]);
});

test('viewer cannot update canvas', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $viewer = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Original Name',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer);

    $response = $this->putJson(route('verse_link_update_canvas', $canvas), [
        'name' => 'Hacked Name',
    ]);

    $response->assertStatus(403);
});

test('viewer can view canvas but not delete it', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $viewer = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Shared Canvas',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $viewer->id,
        'role' => 'viewer',
    ]);

    $this->actingAs($viewer);

    // Can view
    $response = $this->getJson(route('verse_link_show_canvas', $canvas));
    $response->assertStatus(200);

    // Cannot delete
    $response = $this->deleteJson(route('verse_link_destroy_canvas', $canvas));
    $response->assertStatus(403);
});

test('only owner can delete canvas', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $editor = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'To Delete',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $editor->id,
        'role' => 'editor',
    ]);

    $this->actingAs($editor);

    $response = $this->deleteJson(route('verse_link_destroy_canvas', $canvas));
    $response->assertStatus(403);

    // Owner can delete
    $this->actingAs($owner);
    $response = $this->deleteJson(route('verse_link_destroy_canvas', $canvas));
    $response->assertStatus(200);
});

test('owner can remove user permission', function () {
    $owner = User::factory()->create(['onboarding_completed' => true]);
    $user = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Shared Canvas',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $user->id,
        'role' => 'editor',
    ]);

    $this->actingAs($owner);

    $response = $this->deleteJson(route('verse_link_remove_permission', [
        'canvas' => $canvas,
        'user' => $user,
    ]));

    $response->assertStatus(200);
    
    $this->assertDatabaseMissing('canvas_permissions', [
        'canvas_id' => $canvas->id,
        'user_id' => $user->id,
    ]);
});

test('owner can get list of collaborators', function () {
    $owner = User::factory()->create(['onboarding_completed' => true, 'name' => 'Owner']);
    $editor = User::factory()->create(['onboarding_completed' => true, 'name' => 'Editor']);
    $viewer = User::factory()->create(['onboarding_completed' => true, 'name' => 'Viewer']);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $owner->id,
        'name' => 'Shared Canvas',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create(['user_id' => $editor->id, 'role' => 'editor']);
    $canvas->permissions()->create(['user_id' => $viewer->id, 'role' => 'viewer']);

    $this->actingAs($owner);

    $response = $this->getJson(route('verse_link_get_collaborators', $canvas));

    $response->assertStatus(200);
    $response->assertJsonCount(3); // owner + editor + viewer
    
    $collaborators = $response->json();
    expect($collaborators[0]['role'])->toBe('owner');
    expect($collaborators[0]['name'])->toBe('Owner');
});

test('canvas can be used in solo mode without collaboration', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user->id,
        'name' => 'Solo Canvas',
        'is_collaborative' => false,
    ]);

    $this->actingAs($user);

    // Owner can still access and update
    $response = $this->getJson(route('verse_link_show_canvas', $canvas));
    $response->assertStatus(200);

    $response = $this->putJson(route('verse_link_update_canvas', $canvas), [
        'name' => 'Updated Solo Canvas',
    ]);
    $response->assertStatus(200);
    
    // No permissions exist
    expect($canvas->permissions()->count())->toBe(0);
});
