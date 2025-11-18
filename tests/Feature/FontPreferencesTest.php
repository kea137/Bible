<?php

use App\Models\User;

use function Pest\Laravel\actingAs;

test('authenticated user can update font family preference', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/user/font-preferences', [
            'font_family' => 'serif',
            'font_size' => 'base',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'font_family' => 'serif',
            'font_size' => 'base',
        ]);

    $user->refresh();
    expect($user->appearance_preferences['font_family'])->toBe('serif');
    expect($user->appearance_preferences['font_size'])->toBe('base');
});

test('authenticated user can update font size preference', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/user/font-preferences', [
            'font_family' => 'system',
            'font_size' => 'lg',
        ])
        ->assertOk()
        ->assertJson([
            'success' => true,
            'font_family' => 'system',
            'font_size' => 'lg',
        ]);

    $user->refresh();
    expect($user->appearance_preferences['font_size'])->toBe('lg');
});

test('font family validation rejects invalid values', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/user/font-preferences', [
            'font_family' => 'invalid-font',
            'font_size' => 'base',
        ])
        ->assertStatus(422);
});

test('font size validation rejects invalid values', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->postJson('/api/user/font-preferences', [
            'font_family' => 'system',
            'font_size' => 'invalid-size',
        ])
        ->assertStatus(422);
});

test('font preferences require authentication', function () {
    $this->postJson('/api/user/font-preferences', [
        'font_family' => 'serif',
        'font_size' => 'base',
    ])
        ->assertStatus(401);
});

test('font preferences persist with existing appearance preferences', function () {
    $user = User::factory()->create([
        'appearance_preferences' => ['theme' => 'dark'],
    ]);

    actingAs($user)
        ->postJson('/api/user/font-preferences', [
            'font_family' => 'monospace',
            'font_size' => 'xl',
        ])
        ->assertOk();

    $user->refresh();
    expect($user->appearance_preferences['theme'])->toBe('dark');
    expect($user->appearance_preferences['font_family'])->toBe('monospace');
    expect($user->appearance_preferences['font_size'])->toBe('xl');
});
