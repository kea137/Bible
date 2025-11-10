<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bible;
use App\Models\Verse;

class PublicBibleController extends Controller
{
    /**
     * Fetch Bible verses using standardized URL path
     *
     * URL Format: /api/{language}/{version}/{references}/{book}/{chapter}/{verse?}
     *
     * Path parameters:
     * - language: Bible language (e.g., 'English', 'Swahili')
     * - version: Bible version/abbreviation (e.g., 'KJV', 'NIV')
     * - references: Include cross-references ('true' or 'false', or '1' or '0')
     * - book: Book name or number (e.g., 'Genesis' or '1')
     * - chapter: Chapter number
     * - verse: Specific verse number (optional)
     *
     * Examples:
     * /api/English/KJV/false/Genesis/1
     * /api/English/KJV/true/John/3/16
     * /api/English/NIV/false/1/1/1
     */
    public function versesPath($language, $version, $references, $book, $chapter, $verse = null)
    {
        // Build Bible query
        $bibleQuery = Bible::query();

        // Filter by language
        $bibleQuery->where('language', $language);

        // Filter by version
        $bibleQuery->where(function ($query) use ($version) {
            $query->where('abbreviation', $version)
                ->orWhere('version', $version);
        });

        // Get the Bible (use first match)
        $bible = $bibleQuery->first();

        if (! $bible) {
            return response()->json([
                'error' => 'No Bible found matching the specified criteria',
                'message' => 'Please check your language and version parameters',
            ], 404);
        }

        // Find the book (by name or number)
        $bookQuery = $bible->books();

        // Check if book is numeric
        if (is_numeric($book)) {
            $bookQuery->where('book_number', $book);
        } else {
            $bookQuery->where('title', 'LIKE', $book.'%');
        }

        $bookModel = $bookQuery->first();

        if (! $bookModel) {
            return response()->json([
                'error' => 'Book not found',
                'message' => 'The specified book does not exist in this Bible version',
            ], 404);
        }

        // Find the chapter
        $chapterModel = $bookModel->chapters()
            ->where('chapter_number', $chapter)
            ->first();

        if (! $chapterModel) {
            return response()->json([
                'error' => 'Chapter not found',
                'message' => 'The specified chapter does not exist in this book',
            ], 404);
        }

        // Build verse query
        $versesQuery = $chapterModel->verses()
            ->orderBy('verse_number');

        // If specific verse is requested
        if ($verse !== null) {
            $versesQuery->where('verse_number', $verse);
        }

        // Load references if requested
        $includeReferences = in_array(strtolower($references), ['true', '1']);
        if ($includeReferences) {
            $versesQuery->with('references');
        }

        $verses = $versesQuery->get();

        if ($verses->isEmpty()) {
            return response()->json([
                'error' => 'No verses found',
                'message' => 'No verses match the specified criteria',
            ], 404);
        }

        // Format response
        return response()->json([
            'bible' => [
                'name' => $bible->name,
                'abbreviation' => $bible->abbreviation,
                'language' => $bible->language,
                'version' => $bible->version,
            ],
            'book' => [
                'name' => $bookModel->title,
                'number' => $bookModel->book_number,
            ],
            'chapter' => [
                'number' => $chapterModel->chapter_number,
            ],
            'verses' => $verses->map(function ($verse) use ($includeReferences) {
                $verseData = [
                    'number' => $verse->verse_number,
                    'text' => $verse->text,
                ];

                // Include references if requested
                if ($includeReferences && $verse->references) {
                    $verseData['references'] = $verse->references->map(function ($ref) {
                        return [
                            'verse' => $ref->verse,
                            'references' => json_decode($ref->references, true),
                        ];
                    });
                }

                return $verseData;
            }),
            'count' => $verses->count(),
        ]);
    }
}
