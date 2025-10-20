<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bible;
use App\Models\Verse;
use Illuminate\Http\Request;

class PublicBibleController extends Controller
{
    /**
     * Fetch Bible verses with optional filters
     * 
     * Query parameters:
     * - language: Filter by Bible language (e.g., 'English', 'Swahili')
     * - version: Filter by Bible version/abbreviation (e.g., 'KJV', 'NIV')
     * - references: Boolean to include cross-references (default: false)
     * - book: Book name or number (e.g., 'Genesis' or '1')
     * - chapter: Chapter number
     * - verse: Specific verse number (optional)
     * 
     * Examples:
     * /api/public/verses?language=English&version=KJV&book=Genesis&chapter=1
     * /api/public/verses?version=KJV&book=John&chapter=3&verse=16&references=true
     */
    public function verses(Request $request)
    {
        $request->validate([
            'language' => 'nullable|string',
            'version' => 'nullable|string',
            'references' => 'nullable|boolean',
            'book' => 'required|string',
            'chapter' => 'required|integer|min:1',
            'verse' => 'nullable|integer|min:1',
        ]);

        // Build Bible query
        $bibleQuery = Bible::query();

        if ($request->has('language')) {
            $bibleQuery->where('language', $request->language);
        }

        if ($request->has('version')) {
            $bibleQuery->where(function ($query) use ($request) {
                $query->where('abbreviation', $request->version)
                    ->orWhere('version', $request->version);
            });
        }

        // Get the Bible (use first match or default to first available)
        $bible = $bibleQuery->first();

        if (!$bible) {
            return response()->json([
                'error' => 'No Bible found matching the specified criteria',
                'message' => 'Please check your language and version parameters'
            ], 404);
        }

        // Find the book (by name or number)
        $book = $bible->books()
            ->where(function ($query) use ($request) {
                $query->where('title', 'LIKE', $request->book . '%')
                    ->orWhere('book_number', $request->book);
            })
            ->first();

        if (!$book) {
            return response()->json([
                'error' => 'Book not found',
                'message' => 'The specified book does not exist in this Bible version'
            ], 404);
        }

        // Find the chapter
        $chapter = $book->chapters()
            ->where('chapter_number', $request->chapter)
            ->first();

        if (!$chapter) {
            return response()->json([
                'error' => 'Chapter not found',
                'message' => 'The specified chapter does not exist in this book'
            ], 404);
        }

        // Build verse query
        $versesQuery = $chapter->verses()
            ->orderBy('verse_number');

        // If specific verse is requested
        if ($request->has('verse')) {
            $versesQuery->where('verse_number', $request->verse);
        }

        // Load references if requested
        if ($request->boolean('references')) {
            $versesQuery->with('references');
        }

        $verses = $versesQuery->get();

        if ($verses->isEmpty()) {
            return response()->json([
                'error' => 'No verses found',
                'message' => 'No verses match the specified criteria'
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
                'name' => $book->title,
                'number' => $book->book_number,
            ],
            'chapter' => [
                'number' => $chapter->chapter_number,
            ],
            'verses' => $verses->map(function ($verse) use ($request) {
                $verseData = [
                    'number' => $verse->verse_number,
                    'text' => $verse->text,
                ];

                // Include references if requested
                if ($request->boolean('references') && $verse->references) {
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
