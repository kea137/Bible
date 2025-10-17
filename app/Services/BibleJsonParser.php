<?php

namespace App\Services;

use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;

class BibleJsonParser
{
    /**
     * Parse and store Bible data from JSON file
     */
    public function parse(Bible $bible, array $data): void
    {
        $format = $this->detectFormat($data);

        match ($format) {
            'swahili' => $this->parseSwahiliFormat($bible, $data),
            'flat_verses' => $this->parseFlatVersesFormat($bible, $data),
            'nested_books' => $this->parseNestedBooksFormat($bible, $data),
            default => throw new \InvalidArgumentException('Unsupported JSON format'),
        };
    }

    /**
     * Detect the format of the JSON data
     */
    private function detectFormat(array $data): string
    {
        // Check for Swahili format (current implementation)
        if (isset($data['BIBLEBOOK']) && is_array($data['BIBLEBOOK'])) {
            return 'swahili';
        }

        // Check for flat verses format (array of verse objects)
        if (isset($data[0]) && isset($data[0]['book']) && isset($data[0]['chapter']) && isset($data[0]['verse'])) {
            return 'flat_verses';
        }

        // Check for nested books format (array of books with chapters and verses)
        if (isset($data['books']) && is_array($data['books'])) {
            return 'nested_books';
        }

        // Check if it's a direct array of books
        if (isset($data[0]) && isset($data[0]['chapters']) && is_array($data[0]['chapters'])) {
            return 'nested_books';
        }

        throw new \InvalidArgumentException('Unable to detect JSON format');
    }

    /**
     * Parse Swahili format (original implementation)
     */
    private function parseSwahiliFormat(Bible $bible, array $data): void
    {
        foreach ($data['BIBLEBOOK'] as $book) {
            $created_book = Book::create([
                'bible_id' => $bible->id,
                'book_number' => $book['book_number'],
                'title' => $book['book_name'],
                'author' => $book['author'] ?? null,
                'published_year' => $book['published_year'] ?? null,
                'introduction' => $book['introduction'] ?? null,
                'summary' => $book['summary'] ?? null,
            ]);

            foreach ($book['CHAPTER'] as $chapter) {
                if (! is_array($chapter)) {
                    continue;
                }

                $created_chapter = Chapter::create([
                    'bible_id' => $bible->id,
                    'book_id' => $created_book->id,
                    'chapter_number' => $chapter['chapter_number'] ?? 0,
                    'title' => $chapter['title'] ?? null,
                    'introduction' => $chapter['introduction'] ?? null,
                ]);

                if (isset($chapter['VERSES']) && is_array($chapter['VERSES'])) {
                    foreach ($chapter['VERSES'] as $verse) {
                        if (! is_array($verse)) {
                            continue;
                        }
                        $created_chapter->verses()->create([
                            'bible_id' => $bible->id,
                            'book_id' => $created_book->id,
                            'chapter_id' => $created_chapter->id,
                            'verse_number' => $verse['verse_number'],
                            'text' => $verse['verse_text'],
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Parse flat verses format (array of individual verse objects)
     * Format: [{"book": "Genesis", "chapter": 1, "verse": 1, "text": "..."}]
     */
    private function parseFlatVersesFormat(Bible $bible, array $data): void
    {
        $books = [];
        $chapters = [];

        foreach ($data as $verseData) {
            $bookName = $verseData['book'];
            $chapterNumber = $verseData['chapter'];
            $verseNumber = $verseData['verse'];
            $verseText = $verseData['text'];

            // Get or create book
            if (! isset($books[$bookName])) {
                // Try to get book number from common Bible book order
                $bookNumber = $this->getBookNumber($bookName);

                $books[$bookName] = Book::create([
                    'bible_id' => $bible->id,
                    'book_number' => $bookNumber,
                    'title' => $bookName,
                ]);
            }

            $book = $books[$bookName];
            $chapterKey = $bookName.'_'.$chapterNumber;

            // Get or create chapter
            if (! isset($chapters[$chapterKey])) {
                $chapters[$chapterKey] = Chapter::create([
                    'bible_id' => $bible->id,
                    'book_id' => $book->id,
                    'chapter_number' => $chapterNumber,
                ]);
            }

            $chapter = $chapters[$chapterKey];

            // Create verse
            $chapter->verses()->create([
                'bible_id' => $bible->id,
                'book_id' => $book->id,
                'chapter_id' => $chapter->id,
                'verse_number' => $verseNumber,
                'text' => $verseText,
            ]);
        }
    }

    /**
     * Parse nested books format (books array with chapters and verses nested)
     * Format: {"books": [{"name": "Genesis", "chapters": [{"number": 1, "verses": [...]}]}]}
     * Or: [{"name": "Genesis", "chapters": [{"number": 1, "verses": [...]}]}]
     */
    private function parseNestedBooksFormat(Bible $bible, array $data): void
    {
        $booksData = $data['books'] ?? $data;

        foreach ($booksData as $index => $bookData) {
            $bookNumber = $bookData['number'] ?? $bookData['book_number'] ?? ($index + 1);
            $bookName = $bookData['name'] ?? $bookData['title'] ?? $bookData['book'];

            $created_book = Book::create([
                'bible_id' => $bible->id,
                'book_number' => $bookNumber,
                'title' => $bookName,
                'author' => $bookData['author'] ?? null,
            ]);

            $chaptersData = $bookData['chapters'] ?? [];

            foreach ($chaptersData as $chapterData) {
                $chapterNumber = $chapterData['number'] ?? $chapterData['chapter'] ?? $chapterData['chapter_number'] ?? 0;

                $created_chapter = Chapter::create([
                    'bible_id' => $bible->id,
                    'book_id' => $created_book->id,
                    'chapter_number' => $chapterNumber,
                ]);

                $versesData = $chapterData['verses'] ?? [];

                foreach ($versesData as $verseData) {
                    $verseNumber = $verseData['number'] ?? $verseData['verse'] ?? $verseData['verse_number'] ?? 0;
                    $verseText = $verseData['text'] ?? $verseData['verse_text'] ?? '';

                    $created_chapter->verses()->create([
                        'bible_id' => $bible->id,
                        'book_id' => $created_book->id,
                        'chapter_id' => $created_chapter->id,
                        'verse_number' => $verseNumber,
                        'text' => $verseText,
                    ]);
                }
            }
        }
    }

    /**
     * Get book number from common Bible book order
     */
    private function getBookNumber(string $bookName): int
    {
        $books = [
            'Genesis' => 1, 'Exodus' => 2, 'Leviticus' => 3, 'Numbers' => 4, 'Deuteronomy' => 5,
            'Joshua' => 6, 'Judges' => 7, 'Ruth' => 8, '1 Samuel' => 9, '2 Samuel' => 10,
            '1 Kings' => 11, '2 Kings' => 12, '1 Chronicles' => 13, '2 Chronicles' => 14, 'Ezra' => 15,
            'Nehemiah' => 16, 'Esther' => 17, 'Job' => 18, 'Psalms' => 19, 'Proverbs' => 20,
            'Ecclesiastes' => 21, 'Song of Solomon' => 22, 'Isaiah' => 23, 'Jeremiah' => 24, 'Lamentations' => 25,
            'Ezekiel' => 26, 'Daniel' => 27, 'Hosea' => 28, 'Joel' => 29, 'Amos' => 30,
            'Obadiah' => 31, 'Jonah' => 32, 'Micah' => 33, 'Nahum' => 34, 'Habakkuk' => 35,
            'Zephaniah' => 36, 'Haggai' => 37, 'Zechariah' => 38, 'Malachi' => 39,
            'Matthew' => 40, 'Mark' => 41, 'Luke' => 42, 'John' => 43, 'Acts' => 44,
            'Romans' => 45, '1 Corinthians' => 46, '2 Corinthians' => 47, 'Galatians' => 48, 'Ephesians' => 49,
            'Philippians' => 50, 'Colossians' => 51, '1 Thessalonians' => 52, '2 Thessalonians' => 53, '1 Timothy' => 54,
            '2 Timothy' => 55, 'Titus' => 56, 'Philemon' => 57, 'Hebrews' => 58, 'James' => 59,
            '1 Peter' => 60, '2 Peter' => 61, '1 John' => 62, '2 John' => 63, '3 John' => 64,
            'Jude' => 65, 'Revelation' => 66,
        ];

        return $books[$bookName] ?? 0;
    }
}
