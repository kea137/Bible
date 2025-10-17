<?php

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Verse;
use App\Services\BibleJsonParser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('it can parse swahili format json', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = [
        'BIBLEBOOK' => [
            [
                'book_number' => 1,
                'book_name' => 'Genesis',
                'author' => 'Moses',
                'CHAPTER' => [
                    [
                        'chapter_number' => 1,
                        'VERSES' => [
                            ['verse_number' => 1, 'verse_text' => 'In the beginning...'],
                            ['verse_number' => 2, 'verse_text' => 'And the earth was...'],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $parser->parse($bible, $data);

    expect(Book::count())->toBe(1);
    expect(Chapter::count())->toBe(1);
    expect(Verse::count())->toBe(2);
    
    $book = Book::first();
    expect($book->title)->toBe('Genesis');
    expect($book->book_number)->toBe(1);
    expect($book->author)->toBe('Moses');
});

test('it can parse flat verses format json', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = [
        ['book' => 'Genesis', 'chapter' => 1, 'verse' => 1, 'text' => 'In the beginning...'],
        ['book' => 'Genesis', 'chapter' => 1, 'verse' => 2, 'text' => 'And the earth was...'],
        ['book' => 'Genesis', 'chapter' => 2, 'verse' => 1, 'text' => 'Thus the heavens...'],
        ['book' => 'Exodus', 'chapter' => 1, 'verse' => 1, 'text' => 'Now these are the names...'],
    ];

    $parser->parse($bible, $data);

    expect(Book::count())->toBe(2);
    expect(Chapter::count())->toBe(3);
    expect(Verse::count())->toBe(4);
    
    $genesis = Book::where('title', 'Genesis')->first();
    expect($genesis->book_number)->toBe(1);
    expect($genesis->chapters->count())->toBe(2);
    
    $exodus = Book::where('title', 'Exodus')->first();
    expect($exodus->book_number)->toBe(2);
});

test('it can parse nested books format json', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = [
        'books' => [
            [
                'name' => 'Genesis',
                'number' => 1,
                'chapters' => [
                    [
                        'number' => 1,
                        'verses' => [
                            ['number' => 1, 'text' => 'In the beginning...'],
                            ['number' => 2, 'text' => 'And the earth was...'],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Exodus',
                'number' => 2,
                'chapters' => [
                    [
                        'number' => 1,
                        'verses' => [
                            ['number' => 1, 'text' => 'Now these are the names...'],
                        ],
                    ],
                ],
            ],
        ],
    ];

    $parser->parse($bible, $data);

    expect(Book::count())->toBe(2);
    expect(Chapter::count())->toBe(2);
    expect(Verse::count())->toBe(3);
    
    $genesis = Book::where('title', 'Genesis')->first();
    expect($genesis->book_number)->toBe(1);
});

test('it can parse nested books format without books wrapper', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = [
        [
            'name' => 'Genesis',
            'chapters' => [
                [
                    'number' => 1,
                    'verses' => [
                        ['number' => 1, 'text' => 'In the beginning...'],
                    ],
                ],
            ],
        ],
    ];

    $parser->parse($bible, $data);

    expect(Book::count())->toBe(1);
    expect(Chapter::count())->toBe(1);
    expect(Verse::count())->toBe(1);
});

test('it throws exception for unsupported format', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = ['invalid' => 'format'];

    $parser->parse($bible, $data);
})->throws(InvalidArgumentException::class, 'Unable to detect JSON format');

test('it detects swahili format correctly', function () {
    $bible = Bible::factory()->create();
    $parser = new BibleJsonParser();

    $data = ['BIBLEBOOK' => []];

    // Should not throw exception
    expect(fn() => $parser->parse($bible, $data))->not->toThrow(InvalidArgumentException::class);
});
