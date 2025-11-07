<?php

use App\Models\Bible;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('new users without onboarding are redirected to onboarding page', function () {
    $user = User::factory()->create(['onboarding_completed' => false]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('onboarding'));
});

test('users with completed onboarding can access dashboard', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});

test('onboarding page is accessible for users without completed onboarding', function () {
    $user = User::factory()->create(['onboarding_completed' => false]);

    actingAs($user)
        ->get(route('onboarding'))
        ->assertOk();
});

test('users with completed onboarding are redirected from onboarding page', function () {
    $user = User::factory()->create(['onboarding_completed' => true]);

    actingAs($user)
        ->get(route('onboarding'))
        ->assertRedirect(route('dashboard'));
});

test('user can complete onboarding with language selection', function () {
    $user = User::factory()->create(['onboarding_completed' => false]);

    actingAs($user)
        ->post(route('onboarding.store'), [
            'language' => 'sw',
            'preferred_translations' => [],
            'appearance_preferences' => ['theme' => 'dark'],
        ])
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();
    expect($user->language)->toBe('sw');
    expect($user->appearance_preferences)->toBe(['theme' => 'dark']);
});

test('user can complete onboarding with Bible translations', function () {
    $user = User::factory()->create(['onboarding_completed' => false]);
    $bible = Bible::factory()->create();

    actingAs($user)
        ->post(route('onboarding.store'), [
            'language' => 'en',
            'preferred_translations' => [$bible->id],
            'appearance_preferences' => ['theme' => 'system'],
        ])
        ->assertRedirect(route('dashboard'));

    $user->refresh();
    expect($user->onboarding_completed)->toBeTrue();
    expect($user->preferred_translations)->toBe([$bible->id]);
});

test('user can skip onboarding', function () {
    $user = User::factory()->create(['onboarding_completed' => false]);

    actingAs($user)
        ->post(route('onboarding.skip'))
        ->assertRedirect(route('dashboard'));

    // Skipping doesn't mark onboarding as completed
    $user->refresh();
    expect($user->onboarding_completed)->toBeFalse();
});
