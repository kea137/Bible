<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Role;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Create an admin user with role number 1
    $adminRole = Role::factory()->create(['role_number' => 1, 'name' => 'Admin']);
    $this->admin = User::factory()->create();
    $this->admin->roles()->attach($adminRole);

    // Create a regular user without admin role
    $userRole = Role::factory()->create(['role_number' => 3, 'name' => 'User']);
    $this->user = User::factory()->create();
    $this->user->roles()->attach($userRole);

    // Create a test Bible
    $this->bible = Bible::factory()->create([
        'name' => 'Test Bible',
        'abbreviation' => 'TB',
        'language' => 'English',
        'version' => '1.0',
        'description' => 'Test Description',
    ]);
});

test('admin can access configure bibles page', function () {
    actingAs($this->admin)
        ->get('/bibles/configure')
        ->assertOk();
});

test('non-admin cannot access configure bibles page', function () {
    actingAs($this->user)
        ->get('/bibles/configure')
        ->assertForbidden();
});

test('admin can access edit bible page', function () {
    actingAs($this->admin)
        ->get("/bibles/{$this->bible->id}/edit")
        ->assertOk();
});

test('admin can update bible', function () {
    actingAs($this->admin)
        ->put("/bibles/{$this->bible->id}", [
            'name' => 'Updated Bible',
            'abbreviation' => 'UB',
            'language' => 'Swahili',
            'version' => '2.0',
            'description' => 'Updated Description',
        ])
        ->assertRedirect('/bibles/configure')
        ->assertSessionHas('success', 'Bible updated successfully.');

    $this->bible->refresh();
    expect($this->bible->name)->toBe('Updated Bible');
    expect($this->bible->abbreviation)->toBe('UB');
    expect($this->bible->language)->toBe('Swahili');
    expect($this->bible->version)->toBe('2.0');
    expect($this->bible->description)->toBe('Updated Description');
});

test('non-admin cannot update bible', function () {
    actingAs($this->user)
        ->put("/bibles/{$this->bible->id}", [
            'name' => 'Updated Bible',
            'abbreviation' => 'UB',
            'language' => 'Swahili',
            'version' => '2.0',
            'description' => 'Updated Description',
        ])
        ->assertForbidden();
});

test('bible update requires name', function () {
    actingAs($this->admin)
        ->put("/bibles/{$this->bible->id}", [
            'abbreviation' => 'UB',
            'language' => 'Swahili',
            'version' => '2.0',
        ])
        ->assertSessionHasErrors(['name']);
});

test('bible update requires abbreviation', function () {
    actingAs($this->admin)
        ->put("/bibles/{$this->bible->id}", [
            'name' => 'Updated Bible',
            'language' => 'Swahili',
            'version' => '2.0',
        ])
        ->assertSessionHasErrors(['abbreviation']);
});

test('bible update requires language', function () {
    actingAs($this->admin)
        ->put("/bibles/{$this->bible->id}", [
            'name' => 'Updated Bible',
            'abbreviation' => 'UB',
            'version' => '2.0',
        ])
        ->assertSessionHasErrors(['language']);
});

test('bible update requires version', function () {
    actingAs($this->admin)
        ->put("/bibles/{$this->bible->id}", [
            'name' => 'Updated Bible',
            'abbreviation' => 'UB',
            'language' => 'Swahili',
        ])
        ->assertSessionHasErrors(['version']);
});

test('admin can delete bible', function () {
    actingAs($this->admin)
        ->delete("/bibles/{$this->bible->id}")
        ->assertRedirect('/bibles/configure')
        ->assertSessionHas('success', 'Bible deleted successfully.');

    expect(Bible::find($this->bible->id))->toBeNull();
});

test('deleting bible cascades to related books', function () {
    // Create a book for the Bible
    $book = Book::factory()->create(['bible_id' => $this->bible->id]);

    actingAs($this->admin)
        ->delete("/bibles/{$this->bible->id}")
        ->assertRedirect('/bibles/configure');

    expect(Bible::find($this->bible->id))->toBeNull();
    expect(Book::find($book->id))->toBeNull();
});

test('non-admin cannot delete bible', function () {
    actingAs($this->user)
        ->delete("/bibles/{$this->bible->id}")
        ->assertForbidden();
});
