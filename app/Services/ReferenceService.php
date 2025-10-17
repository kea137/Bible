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
            foreach ($data as $verseId => $referenceData) {
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
     */
    public function getReferencesForVerse(Verse $verse): array
    {
        $reference = Reference::where('verse_id', $verse->id)->first();
        
        if (!$reference) {
            return [];
        }

        $references = json_decode($reference->verse_reference, true);
        $result = [];

        foreach ($references as $id => $refString) {
            $refData = BookShorthand::parseReference($refString);
            if (!empty($refData)) {
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
}
