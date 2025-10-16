<?php

use App\Models\User;

test('language can be updated', function () {
    $user = User::factory()->create(['language' => 'en']);

    $response = $this
        ->actingAs($user)
        ->patch(route('language.update'), [
            'language' => 'sw',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $user->refresh();

    expect($user->language)->toBe('sw');
});

test('language update requires valid language code', function () {
    $user = User::factory()->create(['language' => 'en']);

    $response = $this
        ->actingAs($user)
        ->patch(route('language.update'), [
            'language' => 'invalid',
        ]);

    $response->assertSessionHasErrors('language');

    $user->refresh();

    expect($user->language)->toBe('en');
});

test('language update requires authentication', function () {
    $response = $this
        ->patch(route('language.update'), [
            'language' => 'sw',
        ]);

    $response->assertRedirect(route('login'));
});
