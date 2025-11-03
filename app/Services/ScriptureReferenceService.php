<?php

namespace App\Services;

use App\Models\Verse;
use App\Models\Book;

class ScriptureReferenceService
{
    /**
     * Parse scripture references from text
     * Supports: 'REF' for short references and '''REF''' for full verses
     * e.g., '2KI 2:2' or '''GEN 1:1'''
     */
    public function parseReferences(string $text): array
    {
        $references = [];
        
        // Pattern for full verse references: '''BOOK CHAPTER:VERSE'''
        preg_match_all("/'''([A-Z0-9]{3})\s+(\d+):(\d+)'''/", $text, $fullMatches, PREG_SET_ORDER);
        foreach ($fullMatches as $match) {
            $references[] = [
                'type' => 'full',
                'book_code' => $match[1],
                'chapter' => (int)$match[2],
                'verse' => (int)$match[3],
                'original' => $match[0],
            ];
        }
        
        // Pattern for short references: 'BOOK CHAPTER:VERSE'
        preg_match_all("/'([A-Z0-9]{3})\s+(\d+):(\d+)'/", $text, $shortMatches, PREG_SET_ORDER);
        foreach ($shortMatches as $match) {
            $references[] = [
                'type' => 'short',
                'book_code' => $match[1],
                'chapter' => (int)$match[2],
                'verse' => (int)$match[3],
                'original' => $match[0],
            ];
        }
        
        return $references;
    }

    /**
     * Fetch verse data for a reference
     */
    public function fetchVerse(string $bookCode, int $chapterNumber, int $verseNumber, int $bibleId): ?array
    {
        $book = Book::where('code', $bookCode)->where('bible_id', $bibleId)->first();
        
        if (!$book) {
            return null;
        }

        $verse = Verse::whereHas('chapter', function ($query) use ($book, $chapterNumber) {
            $query->where('book_id', $book->id)
                  ->where('chapter_number', $chapterNumber);
        })
        ->where('verse_number', $verseNumber)
        ->with(['chapter.book'])
        ->first();

        if (!$verse) {
            return null;
        }

        return [
            'text' => $verse->text,
            'book_code' => $bookCode,
            'book_title' => $book->title,
            'chapter_number' => $chapterNumber,
            'verse_number' => $verseNumber,
            'reference' => "{$bookCode} {$chapterNumber}:{$verseNumber}",
        ];
    }

    /**
     * Replace scripture references in text with formatted content
     */
    public function replaceReferences(string $text, int $bibleId): string
    {
        $references = $this->parseReferences($text);
        
        foreach ($references as $ref) {
            $verseData = $this->fetchVerse($ref['book_code'], $ref['chapter'], $ref['verse'], $bibleId);
            
            if ($verseData) {
                if ($ref['type'] === 'full') {
                    // Replace full verse references with the actual verse text
                    $replacement = $verseData['text'];
                    $text = str_replace($ref['original'], $replacement, $text);
                } else {
                    // Short references remain as clickable references
                    // They will be handled in the frontend
                }
            }
        }
        
        return $text;
    }
}
