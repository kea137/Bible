<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Reference;
use App\Models\Role;
use App\Models\User;
use App\Models\Verse;

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

    // Create a test Bible with books, chapters, and verses
    $this->bible = Bible::factory()->create([
        'name' => 'Test Bible',
        'abbreviation' => 'TB',
        'language' => 'English',
    ]);

    $this->book = Book::factory()->create(['bible_id' => $this->bible->id]);
    $this->chapter = Chapter::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
    ]);
    $this->verse = Verse::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
    ]);

    // Create some references
    $this->reference1 = Reference::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_id' => $this->verse->id,
    ]);

    $this->reference2 = Reference::factory()->create([
        'bible_id' => $this->bible->id,
        'book_id' => $this->book->id,
        'chapter_id' => $this->chapter->id,
        'verse_id' => $this->verse->id,
    ]);
});

test('admin can access configure references page', function () {
    actingAs($this->admin)
        ->get('/references/configure')
        ->assertOk();
});

test('non-admin cannot access configure references page', function () {
    actingAs($this->user)
        ->get('/references/configure')
        ->assertForbidden();
});

test('configure references page shows bibles with reference counts', function () {
    $response = actingAs($this->admin)
        ->get('/references/configure')
        ->assertOk();

    // The page should contain the Bible name and reference count
    expect($response->viewData('page')['props']['bibles'])->toBeArray();
});

test('admin can delete all references for a bible', function () {
    actingAs($this->admin)
        ->delete("/references/{$this->bible->id}")
        ->assertRedirect('/references/configure')
        ->assertSessionHas('success', 'References deleted successfully.');

    // Check that all references for this Bible are deleted
    expect(Reference::where('bible_id', $this->bible->id)->count())->toBe(0);
});

test('non-admin cannot delete references', function () {
    actingAs($this->user)
        ->delete("/references/{$this->bible->id}")
        ->assertForbidden();

    // Verify references still exist
    expect(Reference::where('bible_id', $this->bible->id)->count())->toBe(2);
});

test('deleting references only affects specified bible', function () {
    // Create another Bible with references
    $otherBible = Bible::factory()->create(['abbreviation' => 'OB']);
    $otherBook = Book::factory()->create(['bible_id' => $otherBible->id]);
    $otherChapter = Chapter::factory()->create([
        'bible_id' => $otherBible->id,
        'book_id' => $otherBook->id,
    ]);
    $otherVerse = Verse::factory()->create([
        'bible_id' => $otherBible->id,
        'book_id' => $otherBook->id,
        'chapter_id' => $otherChapter->id,
    ]);
    $otherReference = Reference::factory()->create([
        'bible_id' => $otherBible->id,
        'book_id' => $otherBook->id,
        'chapter_id' => $otherChapter->id,
        'verse_id' => $otherVerse->id,
    ]);

    actingAs($this->admin)
        ->delete("/references/{$this->bible->id}")
        ->assertRedirect('/references/configure');

    // Check that references for this Bible are deleted
    expect(Reference::where('bible_id', $this->bible->id)->count())->toBe(0);

    // But references for other Bible still exist
    expect(Reference::where('bible_id', $otherBible->id)->count())->toBe(1);
    expect(Reference::find($otherReference->id))->not->toBeNull();
});
