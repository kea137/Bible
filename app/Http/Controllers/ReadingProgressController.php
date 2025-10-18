<?php

namespace App\Http\Controllers;

use App\Models\ReadingProgress;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $progress = ReadingProgress::where('user_id', auth()->id())
            ->where('chapter_id', $validated['chapter_id'])
            ->first();

        if ($progress) {
            // Toggle completion
            $progress->completed = !$progress->completed;
            $progress->completed_at = $progress->completed ? now() : null;
            $progress->save();
        } else {
            // Create new progress record
            $progress = ReadingProgress::create([
                'user_id' => auth()->id(),
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

        $progress = ReadingProgress::where('user_id', auth()->id())
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
        $userId = auth()->id();

        // Total chapters completed across all Bibles
        $totalChaptersCompleted = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();

        // Chapters read today
        $chaptersReadToday = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->count();

        // Total verses read (approximate based on chapters)
        $versesReadToday = $chaptersReadToday * 25; // Rough estimate

        // Most recent reading
        $lastReading = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->with(['bible', 'chapter.book'])
            ->latest('completed_at')
            ->first();

        return response()->json([
            'total_chapters_completed' => $totalChaptersCompleted,
            'chapters_read_today' => $chaptersReadToday,
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
    public function readingPlan()
    {
        $userId = auth()->id();

        // Get progress statistics
        $stats = DB::table('reading_progress')
            ->where('user_id', $userId)
            ->where('completed', true)
            ->selectRaw('
                COUNT(*) as total_chapters_completed,
                COUNT(CASE WHEN DATE(completed_at) = CURDATE() THEN 1 END) as chapters_read_today
            ')
            ->first();

        // Get total chapters in Bible (using first Bible for reference)
        $totalChapters = Chapter::whereHas('book', function ($query) {
            $query->whereHas('bible', function ($q) {
                $q->orderBy('id')->limit(1);
            });
        })->count();

        $completedChapters = $stats->total_chapters_completed ?? 0;
        $progressPercentage = $totalChapters > 0 ? round(($completedChapters / $totalChapters) * 100, 1) : 0;

        return Inertia::render('Reading Plan', [
            'totalChapters' => $totalChapters,
            'completedChapters' => $completedChapters,
            'progressPercentage' => $progressPercentage,
            'chaptersReadToday' => $stats->chapters_read_today ?? 0,
        ]);
    }
}
