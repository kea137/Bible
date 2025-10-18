<?php

use App\Jobs\ProcessBibleUpload;
use App\Models\Bible;
use App\Models\Book;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('bible controller can upload swahili format json file', function () {
    Queue::fake();
    
    $adminRole = Role::create([
        'name' => 'Admin',
        'role_number' => 1,
        'description' => 'Administrator role',
    ]);
    
    $user = User::factory()->create();
    $user->roles()->attach($adminRole);
    
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

    // Assert that the Bible was created with pending status
    expect(Bible::count())->toBe(1);
    $bible = Bible::first();
    expect($bible->name)->toBe('Test Bible');
    expect($bible->status)->toBe('pending');
    
    // Assert that the job was dispatched
    Queue::assertPushed(ProcessBibleUpload::class);
    
    // Process the job to test it works
    $job = new ProcessBibleUpload($bible, json_decode($jsonContent, true), $user->id);
    $job->handle(app(\App\Services\BibleJsonParser::class));
    
    // Now check that books were created
    expect(Book::count())->toBe(1);
    expect($bible->fresh()->status)->toBe('completed');
});

test('bible controller can upload flat verses format json file', function () {
    Queue::fake();
    
    $adminRole = Role::create([
        'name' => 'Admin',
        'role_number' => 1,
        'description' => 'Administrator role',
    ]);
    
    $user = User::factory()->create();
    $user->roles()->attach($adminRole);
    
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

    // Assert that the Bible was created with pending status
    expect(Bible::count())->toBe(1);
    $bible = Bible::first();
    expect($bible->status)->toBe('pending');
    
    // Assert that the job was dispatched
    Queue::assertPushed(ProcessBibleUpload::class);
    
    // Process the job to test it works
    $job = new ProcessBibleUpload($bible, json_decode($jsonContent, true), $user->id);
    $job->handle(app(\App\Services\BibleJsonParser::class));
    
    // Now check that books were created
    expect(Book::count())->toBe(1);
    expect($bible->fresh()->status)->toBe('completed');
});

test('bible controller handles invalid json format gracefully', function () {
    $adminRole = Role::create([
        'name' => 'Admin',
        'role_number' => 1,
        'description' => 'Administrator role',
    ]);
    
    $user = User::factory()->create();
    $user->roles()->attach($adminRole);
    
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
