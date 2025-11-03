<?php

namespace App\Http\Controllers;

use App\Models\Bible;
use App\Models\Chapter;
use App\Models\ReadingProgress;
use App\Models\LessonProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ReadingProgressController extends Controller
{
    /**
     * Toggle chapter completion status
     */
    public function toggleChapter(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'bible_id' => 'required|exists:bibles,id',
        ]);

        $progress = ReadingProgress::where('user_id', Auth::id())
            ->where('chapter_id', $validated['chapter_id'])
            ->first();

        if ($progress) {
            // Toggle completion
            $progress->completed = ! $progress->completed;
            $progress->completed_at = $progress->completed ? now() : null;
            $progress->save();
        } else {
            // Create new progress record
            $progress = ReadingProgress::create([
                'user_id' => Auth::id(),
                'bible_id' => $validated['bible_id'],
                'chapter_id' => $validated['chapter_id'],
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Get progress for a specific Bible
     */
    public function getBibleProgress(Request $request)
    {
        $validated = $request->validate([
            'bible_id' => 'required|exists:bibles,id',
        ]);

        $progress = ReadingProgress::where('user_id', Auth::id())
            ->where('bible_id', $validated['bible_id'])
            ->where('completed', true)
            ->with('chapter')
            ->get()
            ->keyBy('chapter_id');

        return response()->json($progress);
    }

    /**
     * Get overall reading statistics for a user
     */
    public function getStatistics(Request $request)
    {
        $userId = Auth::id();

        // Total chapters completed across all Bibles
        $totalChaptersCompleted = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();

        // Chapters read today with accurate verse count
        $chaptersReadToday = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->with('chapter.verses')
            ->get();

        // Calculate actual verses read today
        $versesReadToday = $chaptersReadToday->sum(function ($progress) {
            return $progress->chapter->verses->count();
        });

        // Most recent reading
        $lastReading = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->with(['bible', 'chapter.book'])
            ->latest('completed_at')
            ->first();

        return response()->json([
            'total_chapters_completed' => $totalChaptersCompleted,
            'chapters_read_today' => $chaptersReadToday->count(),
            'verses_read_today' => $versesReadToday,
            'last_reading' => $lastReading ? [
                'bible_id' => $lastReading->bible_id,
                'bible_name' => $lastReading->bible->name,
                'book_title' => $lastReading->chapter->book->title,
                'chapter_number' => $lastReading->chapter->chapter_number,
            ] : null,
        ]);
    }

    /**
     * Show reading plan page
     */
    public function readingPlan(Request $request)
    {
        $userId = Auth::id();

        // Get selected Bible ID from request or use the first Bible or last read Bible
        $selectedBibleId = $request->input('bible_id');

        if (! $selectedBibleId) {
            // Try to get the last read Bible
            $lastReading = ReadingProgress::where('user_id', $userId)
                ->where('completed', true)
                ->latest('completed_at')
                ->first();

            $selectedBibleId = $lastReading ? $lastReading->bible_id : Bible::orderBy('id')->value('id');
        }

        $selectedBible = Bible::find($selectedBibleId);
        $allBibles = Bible::all();

        // Get total chapters in the selected Bible only
        $totalChapters = Chapter::whereHas('book', function ($query) use ($selectedBibleId) {
            $query->where('bible_id', $selectedBibleId);
        })->count();

        // Get progress statistics for the selected Bible only
        $completedChapters = ReadingProgress::where('user_id', $userId)
            ->where('bible_id', $selectedBibleId)
            ->where('completed', true)
            ->count();

        $chaptersReadToday = ReadingProgress::where('user_id', $userId)
            ->where('bible_id', $selectedBibleId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->count();

        $progressPercentage = $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100, 1) : 0;

        // Get lesson progress
        $completedLessons = LessonProgress::where('user_id', $userId)
            ->where('completed', true)
            ->with('lesson.series')
            ->get();

        $lessonsReadToday = LessonProgress::where('user_id', $userId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->count();

        return Inertia::render('Reading Plan', [
            'totalChapters' => $totalChapters,
            'completedChapters' => $completedChapters,
            'progressPercentage' => $progressPercentage,
            'chaptersReadToday' => $chaptersReadToday,
            'selectedBible' => $selectedBible,
            'allBibles' => $allBibles,
            'completedLessons' => $completedLessons,
            'lessonsReadToday' => $lessonsReadToday,
        ]);
    }
}
