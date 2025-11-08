<?php

use App\Models\Bible;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('user can update preferred translations', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);
    $bible1 = Bible::factory()->create();
    $bible2 = Bible::factory()->create();

    actingAs($user)
        ->post(route('update_translations'), [
            'preferred_translations' => [$bible1->id, $bible2->id],
        ])
        ->assertOk()
        ->assertJson(['success' => true]);

    $user->refresh();
    expect($user->preferred_translations)->toBe([$bible1->id, $bible2->id]);
});

test('user can update preferred translations with empty array', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'preferred_translations' => [1, 2, 3],
    ]);

    actingAs($user)
        ->post(route('update_translations'), [
            'preferred_translations' => [],
        ])
        ->assertOk();

    $user->refresh();
    expect($user->preferred_translations)->toBe([]);
});

test('user can update preferred translations with null', function () {
    $user = User::factory()->create([
        'onboarding_completed' => true,
        'preferred_translations' => [1, 2, 3],
    ]);

    actingAs($user)
        ->post(route('update_translations'), [
            'preferred_translations' => null,
        ])
        ->assertOk();

    $user->refresh();
    expect($user->preferred_translations)->toBe([]);
});

test('preferred translations must be valid bible IDs', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);

    actingAs($user)
        ->post(route('update_translations'), [
            'preferred_translations' => [999999],
        ])
        ->assertSessionHasErrors('preferred_translations.0');
});

test('unauthenticated user cannot update translations', function () {
    $this->post(route('update_translations'), [
        'preferred_translations' => [1, 2],
    ])
        ->assertRedirect(route('login'));
});

test('preferences page is accessible', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);

    actingAs($user)
        ->get(route('appearance.edit'))
        ->assertOk();
});
