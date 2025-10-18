<?php

namespace App\Services;

use App\Models\Bible;
use App\Models\Reference;
use App\Models\Verse;
use App\Utils\BookShorthand;
use Illuminate\Support\Facades\DB;

class ReferenceService
{
    /**
     * Load references from JSON data
     * Format: {"1":{"v":"GEN 1 1","r":{"2063":"EXO 20 11",...}}}
     */
    public function loadFromJson(Bible $bible, array $data): void
    {
        DB::transaction(function () use ($bible, $data) {
            foreach ($data as $referenceData) {

                // Ensure required keys exist
                if (!isset($referenceData['v']) || !isset($referenceData['r'])) {
                    continue;
                }
                
                // Parse the verse reference
                $verseRef = BookShorthand::parseReference($referenceData['v']);
                if (empty($verseRef)) {
                    continue;
                }
                
                // Find the verse in the database
                $verse = $this->findVerseByReference($bible, $verseRef);
                if (!$verse) {
                    continue;
                }

                // Process all references for this verse
                $references = [];
                foreach ($referenceData['r'] as $refId => $refString) {
                    $refData = BookShorthand::parseReference($refString);
                    if (!empty($refData)) {
                        $references[$refId] = $refString;
                    }
                }
                
                // Store the reference
                Reference::updateOrCreate(
                    [
                        'bible_id' => $bible->id,
                        'verse_id' => $verse->id,
                    ],
                    [
                        'book_id' => $verse->book_id,
                        'chapter_id' => $verse->chapter_id,
                        'verse_reference' => json_encode($references),
                    ]
                );
            }
        });
    }

    /**
     * Get references for a specific verse
     * References are loaded from the first created Bible
     */
    public function getReferencesForVerse(Verse $verse): array
    {
        // Load the verse and its related data
        $verse->load(['book', 'chapter', 'bible']);
        
        // Find the first created Bible
        $firstBible = Bible::orderBy('id', 'asc')->first();
        
        if (!$firstBible) {
            return [];
        }
        
        // Find the equivalent verse in the first Bible
        $firstBibleVerse = $this->findVerseInBible(
            $firstBible,
            $verse->book->book_number,
            $verse->chapter->chapter_number,
            $verse->verse_number
        );
        
        if (!$firstBibleVerse) {
            return [];
        }
        
        // Get the reference for this verse from the first Bible
        $reference = Reference::where('verse_id', $firstBibleVerse->id)
            ->where('bible_id', $firstBible->id)
            ->first();
        
        if (!$reference) {
            return [];
        }

        $references = json_decode($reference->verse_reference, true);
        $result = [];

        foreach ($references as $id => $refString) {
            $refData = BookShorthand::parseReference($refString);
            if (!empty($refData)) {
                // Find the reference verse in the current Bible being viewed
                $refVerse = $this->findVerseByReference($verse->bible, $refData);
                if ($refVerse) {
                    $result[] = [
                        'id' => $id,
                        'reference' => $refString,
                        'verse' => $refVerse,
                        'parsed' => $refData,
                    ];
                }
            }
        }

        return $result;
    }

    /**
     * Find a verse in a specific Bible by book number, chapter number, and verse number
     */
    private function findVerseInBible(Bible $bible, int $bookNumber, int $chapterNumber, int $verseNumber): ?Verse
    {
        return Verse::whereHas('book', function ($query) use ($bookNumber, $bible) {
            $query->where('bible_id', $bible->id)
                  ->where('book_number', $bookNumber);
        })
        ->whereHas('chapter', function ($query) use ($chapterNumber) {
            $query->where('chapter_number', $chapterNumber);
        })
        ->where('verse_number', $verseNumber)
        ->first();
    }

    /**
     * Find a verse by reference (book shorthand, chapter, verse number)
     */
    private function findVerseByReference(Bible $bible, array $ref): ?Verse
    {
        $bookNumber = BookShorthand::getBookNumber($ref['book']);
        if (!$bookNumber) {
            return null;
        }

        return Verse::whereHas('book', function ($query) use ($bookNumber, $bible) {
            $query->where('bible_id', $bible->id)
                  ->where('book_number', $bookNumber);
        })
        ->whereHas('chapter', function ($query) use ($ref) {
            $query->where('chapter_number', $ref['chapter']);
        })
        ->where('verse_number', $ref['verse'])
        ->first();
    }

    /**
     * Get verse by ID with all references
     */
    public function getVerseWithReferences(int $verseId): ?array
    {
        $verse = Verse::with(['book', 'chapter'])->find($verseId);
        if (!$verse) {
            return null;
        }

        return [
            'verse' => $verse,
            'references' => $this->getReferencesForVerse($verse),
        ];
    }

    /**
     * Show verse study page
     */
    public function studyVerse(Verse $verse)
    {
        $verse->load(['book', 'chapter', 'bible']);
        
        // Get references for this verse
        $references = $this->getReferencesForVerse($verse);
        
        // Get other Bible versions of the same verse
        $otherVersions = Verse::whereHas('book', function ($query) use ($verse) {
            $query->where('book_number', $verse->book->book_number);
        })
        ->whereHas('chapter', function ($query) use ($verse) {
            $query->where('chapter_number', $verse->chapter->chapter_number);
        })
        ->where('verse_number', $verse->verse_number)
        ->where('bible_id', '!=', $verse->bible_id)
        ->with(['bible'])
        ->get();
        
        return \Inertia\Inertia::render('Verse Study', [
            'verse' => $verse->toArray(),
            'references' => $references,
            'otherVersions' => $otherVersions->toArray(),
        ]);
    }
}
