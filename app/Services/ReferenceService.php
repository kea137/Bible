<?php

namespace App\Services;

use App\Models\Bible;
use App\Models\Reference;
use App\Models\Verse;
use App\Utils\BookShorthand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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
                if (! isset($referenceData['v']) || ! isset($referenceData['r'])) {
                    continue;
                }

                // Parse the verse reference
                $verseRef = BookShorthand::parseReference($referenceData['v']);
                if (empty($verseRef)) {
                    continue;
                }

                // Find the verse in the database
                $verse = $this->findVerseByReference($bible, $verseRef);
                if (! $verse) {
                    continue;
                }

                // Process all references for this verse
                $references = [];
                foreach ($referenceData['r'] as $refId => $refString) {
                    $refData = BookShorthand::parseReference($refString);
                    if (! empty($refData)) {
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

                // Invalidate cache for this verse across all Bibles
                $this->invalidateVerseReferences($verse);
            }
        });
    }

    /**
     * Invalidate cache for a specific verse across all Bibles
     */
    public function invalidateVerseReferences(Verse $verse): void
    {
        $cacheDriver = config('cache.default');
        $supportsTags = in_array($cacheDriver, ['redis', 'memcached']);

        if ($supportsTags) {
            // If tags are supported, flush the entire tag
            Cache::tags(['verse_references'])->flush();
        } else {
            // Find all equivalent verses across all Bibles in a single query
            $bookNumber = $verse->book->book_number;
            $chapterNumber = $verse->chapter->chapter_number;
            $verseNumber = $verse->verse_number;

            // Get all verses that match this book/chapter/verse across all Bibles
            $equivalentVerses = Verse::select('verses.id', 'verses.bible_id')
                ->join('books', 'verses.book_id', '=', 'books.id')
                ->join('chapters', 'verses.chapter_id', '=', 'chapters.id')
                ->where('books.book_number', $bookNumber)
                ->where('chapters.chapter_number', $chapterNumber)
                ->where('verses.verse_number', $verseNumber)
                ->get();

            // Invalidate cache for each equivalent verse
            foreach ($equivalentVerses as $equivalentVerse) {
                $cacheKey = "verse_references:{$equivalentVerse->id}:{$equivalentVerse->bible_id}";
                Cache::forget($cacheKey);
            }
        }
    }

    /**
     * Clear all verse reference caches
     */
    public function clearAllReferenceCaches(): void
    {
        $cacheDriver = config('cache.default');
        $supportsTags = in_array($cacheDriver, ['redis', 'memcached']);

        if ($supportsTags) {
            Cache::tags(['verse_references'])->flush();
        } else {
            // For cache drivers without tag support, we clear only verse reference keys
            // Use chunking to avoid memory issues with large datasets
            $bibleIds = Bible::pluck('id')->all();
            
            Verse::select('id', 'bible_id')->chunk(1000, function ($verses) use ($bibleIds) {
                $keysToDelete = [];
                foreach ($verses as $verse) {
                    foreach ($bibleIds as $bibleId) {
                        $keysToDelete[] = "verse_references:{$verse->id}:{$bibleId}";
                    }
                }
                
                // Batch delete cache keys for better performance
                foreach ($keysToDelete as $key) {
                    Cache::forget($key);
                }
            });
        }
    }

    /**
     * Get references for a specific verse
     * References are loaded from the first created Bible
     */
    public function getReferencesForVerse(Verse $verse): array
    {
        // Create a cache key based on verse ID and Bible ID
        $cacheKey = "verse_references:{$verse->id}:{$verse->bible_id}";

        // Try to get from cache, with a 1-hour TTL
        // Use tags if supported (redis/memcached), otherwise use simple cache
        $cacheDriver = config('cache.default');
        $supportsTags = in_array($cacheDriver, ['redis', 'memcached']);

        if ($supportsTags) {
            return Cache::tags(['verse_references'])->remember($cacheKey, 3600, function () use ($verse) {
                return $this->fetchReferencesForVerse($verse);
            });
        } else {
            return Cache::remember($cacheKey, 3600, function () use ($verse) {
                return $this->fetchReferencesForVerse($verse);
            });
        }
    }

    /**
     * Fetch references for a verse (helper method for caching)
     */
    private function fetchReferencesForVerse(Verse $verse): array
    {
        // Load the verse and its related data
        $verse->load(['book', 'chapter', 'bible']);

        // Find the first created Bible
        $firstBible = Bible::orderBy('id', 'asc')->first();

        if (! $firstBible) {
            return [];
        }

        // Find the equivalent verse in the first Bible
        $firstBibleVerse = $this->findVerseInBible(
            $firstBible,
            $verse->book->book_number,
            $verse->chapter->chapter_number,
            $verse->verse_number
        );

        if (! $firstBibleVerse) {
            return [];
        }

        // Get the reference for this verse from the first Bible
        $reference = Reference::where('verse_id', $firstBibleVerse->id)
            ->where('bible_id', $firstBible->id)
            ->first();

        if (! $reference) {
            return [];
        }

        $references = json_decode($reference->verse_reference, true);

        // Validate that decoded JSON is an array
        if (! is_array($references)) {
            return [];
        }

        // Pre-fetch all reference verses in a single query to avoid N+1
        $refData = [];
        foreach ($references as $id => $refString) {
            $parsed = BookShorthand::parseReference($refString);
            if (! empty($parsed)) {
                $refData[$id] = [
                    'reference' => $refString,
                    'parsed' => $parsed,
                ];
            }
        }

        // Build queries to fetch all reference verses with eager loading
        // Note: This still executes individual queries per reference verse due to complex
        // whereHas conditions. A full N+1 elimination would require refactoring to:
        // 1. Collect all book_number, chapter_number, verse_number combinations
        // 2. Build a single query with WHERE IN or UNION
        // 3. Match results back to references
        // Current implementation prioritizes code maintainability and already includes
        // eager loading to prevent additional N+1 queries for book/chapter relationships.
        $result = [];

        foreach ($refData as $id => $data) {
            $parsed = $data['parsed'];
            $bookNumber = BookShorthand::getBookNumber($parsed['book']);
            if ($bookNumber) {
                // Find verses matching the criteria
                $refVerse = Verse::with(['book', 'chapter'])
                    ->whereHas('book', function ($query) use ($bookNumber, $verse) {
                        $query->where('bible_id', $verse->bible_id)
                            ->where('book_number', $bookNumber);
                    })
                    ->whereHas('chapter', function ($query) use ($parsed, $verse) {
                        $query->where('bible_id', $verse->bible_id)
                            ->where('chapter_number', $parsed['chapter']);
                    })
                    ->where('verse_number', $parsed['verse'])
                    ->first();

                if ($refVerse) {
                    $result[] = [
                        'id' => $id,
                        'reference' => $data['reference'],
                        'verse' => $refVerse,
                        'parsed' => $parsed,
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
            ->whereHas('chapter', function ($query) use ($chapterNumber, $bible) {
                $query->where('bible_id', $bible->id)
                    ->where('chapter_number', $chapterNumber);
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
        if (! $bookNumber) {
            return null;
        }

        return Verse::whereHas('book', function ($query) use ($bookNumber, $bible) {
            $query->where('bible_id', $bible->id)
                ->where('book_number', $bookNumber);
        })
            ->whereHas('chapter', function ($query) use ($ref, $bible) {
                $query->where('bible_id', $bible->id)
                    ->where('chapter_number', $ref['chapter']);
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
        if (! $verse) {
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
        // Get user's preferred Bible IDs (assume from authenticated user or passed in)
        $user = Auth::getUser();
        $preferredBibleIds = $user->preferred_translations ?? [1, 2, 3, 4, 5];

        $otherVersions = Verse::whereHas('book', function ($query) use ($verse) {
            $query->where('book_number', $verse->book->book_number);
        })
            ->whereHas('chapter', function ($query) use ($verse) {
                $query->where('chapter_number', $verse->chapter->chapter_number);
            })
            ->where('verse_number', $verse->verse_number)
            ->whereIn('bible_id', $preferredBibleIds)
            ->where('bible_id', '!=', $verse->bible_id)
            ->with(['bible'])
            ->limit(5)
            ->get();

        return \Inertia\Inertia::render('Verse Study', [
            'verse' => $verse->toArray(),
            'references' => $references,
            'otherVersions' => $otherVersions->toArray(),
        ]);
    }
}
