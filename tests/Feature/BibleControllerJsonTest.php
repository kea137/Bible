<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('bible controller can upload swahili format json file', function () {
    $user = User::factory()->create();
    Storage::fake('local');

    $jsonContent = json_encode([
        'BIBLEBOOK' => [
            [
                'book_number' => 1,
                'book_name' => 'Genesis',
                'CHAPTER' => [
                    [
                        'chapter_number' => 1,
                        'VERSES' => [
                            ['verse_number' => 1, 'verse_text' => 'Test verse'],
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $file = UploadedFile::fake()->createWithContent('bible.json', $jsonContent);

    $response = $this->actingAs($user)->post(route('bible_store'), [
        'name' => 'Test Bible',
        'abbreviation' => 'TB',
        'language' => 'English',
        'version' => 'Test Version',
        'description' => 'A test bible',
        'file' => $file,
    ]);

    expect(Bible::count())->toBe(1);
    expect(Book::count())->toBe(1);

    $bible = Bible::first();
    expect($bible->name)->toBe('Test Bible');
});

test('bible controller can upload flat verses format json file', function () {
    $user = User::factory()->create();
    Storage::fake('local');

    $jsonContent = json_encode([
        ['book' => 'Genesis', 'chapter' => 1, 'verse' => 1, 'text' => 'Test verse'],
        ['book' => 'Genesis', 'chapter' => 1, 'verse' => 2, 'text' => 'Test verse 2'],
    ]);

    $file = UploadedFile::fake()->createWithContent('bible.json', $jsonContent);

    $response = $this->actingAs($user)->post(route('bible_store'), [
        'name' => 'Test Bible Flat',
        'abbreviation' => 'TBF',
        'language' => 'English',
        'version' => 'Test Version',
        'file' => $file,
    ]);

    expect(Bible::count())->toBe(1);
    expect(Book::count())->toBe(1);
});

test('bible controller handles invalid json format gracefully', function () {
    $user = User::factory()->create();
    Storage::fake('local');

    $jsonContent = json_encode(['invalid' => 'format']);

    $file = UploadedFile::fake()->createWithContent('bible.json', $jsonContent);

    $response = $this->actingAs($user)->post(route('bible_store'), [
        'name' => 'Test Bible Invalid',
        'abbreviation' => 'TBI',
        'language' => 'English',
        'version' => 'Test Version',
        'file' => $file,
    ]);

    // Bible should not be created due to invalid format
    expect(Bible::count())->toBe(0);
    
    // Should redirect back with error
    $response->assertSessionHasErrors('file');
});
