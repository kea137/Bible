<?php

namespace App\Http\Controllers;

use App\Models\Bible;
use App\Models\Verse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get user's preferred language
        $userLanguage = $request->cookie('language', 'en');
        $languageMap = [
            'en' => 'English',
            'sw' => 'Swahili',
        ];
        
        $languageName = $languageMap[$userLanguage] ?? 'English';
        
        // Get random verse of the day
        $randomVerse = Verse::with(['bible', 'book', 'chapter'])
            ->whereHas('bible', function ($query) use ($languageName) {
                $query->where('language', $languageName);
            })
            ->inRandomOrder()
            ->first();
        
        // Get total Bibles in user's language
        $totalBibles = Bible::where('language', $languageName)->count();
        
        // Get last reading from reading progress
        $lastReadingProgress = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->with(['bible', 'chapter.book'])
            ->latest('completed_at')
            ->first();
        
        $lastReading = null;
        if ($lastReadingProgress) {
            $lastReading = [
                'bible_id' => $lastReadingProgress->bible_id,
                'bible_name' => $lastReadingProgress->bible->name,
                'book_title' => $lastReadingProgress->chapter->book->title,
                'chapter_number' => $lastReadingProgress->chapter->chapter_number,
            ];
        }
        
        // Get reading stats
        $totalChaptersCompleted = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->count();
        
        $chaptersReadToday = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->count();
        
        $versesReadToday = $chaptersReadToday * 25; // Rough estimate
        
        $readingStats = [
            'total_bibles' => $totalBibles,
            'verses_read_today' => $versesReadToday,
            'chapters_completed' => $totalChaptersCompleted,
        ];
        
        return Inertia::render('Dashboard', [
            'verseOfTheDay' => $randomVerse,
            'lastReading' => $lastReading,
            'readingStats' => $readingStats,
            'userName' => $user->name,
        ]);
    }
}
