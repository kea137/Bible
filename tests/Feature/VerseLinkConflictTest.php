<?php

use App\Models\User;
use App\Models\VerseLinkCanvas;
use App\Models\VerseLinkNode;
use App\Models\Verse;

beforeEach(function () {
    // Disable search indexing for tests
    config(['scout.driver' => 'null']);
});

test('node update with correct version succeeds', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user->id,
        'name' => 'Test Canvas',
    ]);

    $verse = Verse::factory()->create();

    $node = VerseLinkNode::create([
        'canvas_id' => $canvas->id,
        'verse_id' => $verse->id,
        'position_x' => 100,
        'position_y' => 100,
        'version' => 1,
        'last_modified_by' => $user->id,
        'last_modified_at' => now(),
    ]);

    $this->actingAs($user);

    $response = $this->putJson(route('verse_link_update_node', $node), [
        'position_x' => 200,
        'version' => 1, // Correct version
    ]);

    $response->assertStatus(200);
    $response->assertJson(['success' => true]);

    $node->refresh();
    expect($node->position_x)->toBe(200);
    expect($node->version)->toBe(2); // Version incremented
});

test('node update with outdated version fails with conflict', function () {
    $user1 = User::factory()->create(['onboarding_completed' => true]);
    $user2 = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user1->id,
        'name' => 'Shared Canvas',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $user2->id,
        'role' => 'editor',
    ]);

    $verse = Verse::factory()->create();

    $node = VerseLinkNode::create([
        'canvas_id' => $canvas->id,
        'verse_id' => $verse->id,
        'position_x' => 100,
        'position_y' => 100,
        'version' => 1,
        'last_modified_by' => $user1->id,
        'last_modified_at' => now(),
    ]);

    // User 1 updates the node (version goes from 1 to 2)
    $this->actingAs($user1);
    $this->putJson(route('verse_link_update_node', $node), [
        'position_x' => 150,
        'version' => 1,
    ])->assertStatus(200);

    $node->refresh();
    expect($node->version)->toBe(2);

    // User 2 tries to update with outdated version (still thinks it's version 1)
    $this->actingAs($user2);
    $response = $this->putJson(route('verse_link_update_node', $node), [
        'position_x' => 200,
        'version' => 1, // Outdated version
    ]);

    $response->assertStatus(409); // Conflict
    $response->assertJson([
        'success' => false,
        'error' => 'version_conflict',
    ]);

    // Node should still have user1's changes
    $node->refresh();
    expect($node->position_x)->toBe(150);
    expect($node->version)->toBe(2);
});

test('node update tracks last modified user', function () {
    $user1 = User::factory()->create(['onboarding_completed' => true]);
    $user2 = User::factory()->create(['onboarding_completed' => true]);
    
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user1->id,
        'name' => 'Shared Canvas',
        'is_collaborative' => true,
    ]);

    $canvas->permissions()->create([
        'user_id' => $user2->id,
        'role' => 'editor',
    ]);

    $verse = Verse::factory()->create();

    $node = VerseLinkNode::create([
        'canvas_id' => $canvas->id,
        'verse_id' => $verse->id,
        'position_x' => 100,
        'position_y' => 100,
        'version' => 1,
        'last_modified_by' => $user1->id,
        'last_modified_at' => now(),
    ]);

    $this->actingAs($user2);
    $response = $this->putJson(route('verse_link_update_node', $node), [
        'position_x' => 200,
        'version' => 1,
    ]);

    $response->assertStatus(200);

    $node->refresh();
    expect($node->last_modified_by)->toBe($user2->id);
    expect($node->last_modified_at)->not->toBeNull();
});

test('new nodes are created with version 1', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $canvas = VerseLinkCanvas::create([
        'user_id' => $user->id,
        'name' => 'Test Canvas',
    ]);

    $verse = Verse::factory()->create();

    $this->actingAs($user);

    $response = $this->postJson(route('verse_link_store_node'), [
        'canvas_id' => $canvas->id,
        'verse_id' => $verse->id,
        'position_x' => 100,
        'position_y' => 100,
    ]);

    $response->assertStatus(201);
    $response->assertJson(['success' => true]);

    $node = VerseLinkNode::where('canvas_id', $canvas->id)->first();
    expect($node->version)->toBe(1);
    expect($node->last_modified_by)->toBe($user->id);
});
