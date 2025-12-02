<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MemoryVerse;
use App\Models\Verse;
use App\Services\SpacedRepetitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemoryVerseController extends Controller
{
    protected SpacedRepetitionService $spacedRepetitionService;

    public function __construct(SpacedRepetitionService $spacedRepetitionService)
    {
        $this->spacedRepetitionService = $spacedRepetitionService;
    }

    /**
     * Get all memory verses for authenticated user
     */
    public function index(): JsonResponse
    {
        $memoryVerses = MemoryVerse::with('verse.book', 'verse.chapter')
            ->where('user_id', Auth::id())
            ->orderBy('next_review_date')
            ->get()
            ->map(function ($memoryVerse) {
                return [
                    'id' => $memoryVerse->id,
                    'verse_id' => $memoryVerse->verse_id,
                    'verse_text' => $memoryVerse->verse->text,
                    'book_name' => $memoryVerse->verse->book->name,
                    'chapter_number' => $memoryVerse->verse->chapter->chapter_number,
                    'verse_number' => $memoryVerse->verse->verse_number,
                    'next_review_date' => $memoryVerse->next_review_date->toDateString(),
                    'last_reviewed_at' => $memoryVerse->last_reviewed_at?->toDateString(),
                    'is_due' => $memoryVerse->isDue(),
                    'success_rate' => $memoryVerse->success_rate,
                    'total_reviews' => $memoryVerse->total_reviews,
                ];
            });

        return response()->json([
            'memory_verses' => $memoryVerses,
        ]);
    }

    /**
     * Mark a verse as a memory verse
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'verse_id' => 'required|exists:verses,id',
        ]);

        $verse = Verse::findOrFail($request->verse_id);

        // Check if already a memory verse
        $existing = MemoryVerse::where('user_id', Auth::id())
            ->where('verse_id', $request->verse_id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'This verse is already marked as a memory verse',
                'memory_verse' => $existing,
            ], 409);
        }

        $memoryVerse = $this->spacedRepetitionService->createMemoryVerse(
            Auth::id(),
            $request->verse_id
        );

        $memoryVerse->load('verse.book', 'verse.chapter');

        return response()->json([
            'message' => 'Verse marked as memory verse successfully',
            'memory_verse' => [
                'id' => $memoryVerse->id,
                'verse_id' => $memoryVerse->verse_id,
                'verse_text' => $memoryVerse->verse->text,
                'book_name' => $memoryVerse->verse->book->name,
                'chapter_number' => $memoryVerse->verse->chapter->chapter_number,
                'verse_number' => $memoryVerse->verse->verse_number,
                'next_review_date' => $memoryVerse->next_review_date->toDateString(),
            ],
        ], 201);
    }

    /**
     * Get due memory verses for review
     */
    public function due(): JsonResponse
    {
        $dueVerses = $this->spacedRepetitionService->getDueMemoryVerses(Auth::id());

        $formattedVerses = $dueVerses->map(function ($memoryVerse) {
            return [
                'id' => $memoryVerse->id,
                'verse_id' => $memoryVerse->verse_id,
                'verse_text' => $memoryVerse->verse->text,
                'book_name' => $memoryVerse->verse->book->name,
                'chapter_number' => $memoryVerse->verse->chapter->chapter_number,
                'verse_number' => $memoryVerse->verse->verse_number,
                'next_review_date' => $memoryVerse->next_review_date->toDateString(),
                'repetitions' => $memoryVerse->repetitions,
            ];
        });

        return response()->json([
            'due_verses' => $formattedVerses,
            'count' => $formattedVerses->count(),
        ]);
    }

    /**
     * Submit a review for a memory verse
     */
    public function review(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quality' => 'required|integer|min:0|max:5',
        ]);

        $memoryVerse = MemoryVerse::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $this->spacedRepetitionService->updateReviewSchedule(
            $memoryVerse,
            $request->quality
        );

        $memoryVerse->load('verse.book', 'verse.chapter');

        return response()->json([
            'message' => 'Review submitted successfully',
            'memory_verse' => [
                'id' => $memoryVerse->id,
                'verse_id' => $memoryVerse->verse_id,
                'next_review_date' => $memoryVerse->next_review_date->toDateString(),
                'interval' => $memoryVerse->interval,
                'repetitions' => $memoryVerse->repetitions,
                'success_rate' => $memoryVerse->success_rate,
            ],
        ]);
    }

    /**
     * Get memory verse statistics
     */
    public function statistics(): JsonResponse
    {
        $stats = $this->spacedRepetitionService->getStatistics(Auth::id());

        return response()->json($stats);
    }

    /**
     * Remove a memory verse
     */
    public function destroy(int $id): JsonResponse
    {
        $memoryVerse = MemoryVerse::where('user_id', Auth::id())
            ->where('id', $id)
            ->firstOrFail();

        $memoryVerse->delete();

        return response()->json([
            'message' => 'Memory verse removed successfully',
        ]);
    }
}
