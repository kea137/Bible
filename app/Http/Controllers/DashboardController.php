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
        
        // Get last reading (placeholder - can be implemented with user reading history)
        $lastReading = null;
        
        // Get reading stats (placeholder for future enhancement)
        $readingStats = [
            'total_bibles' => $totalBibles,
            'verses_read_today' => 0, // Placeholder
            'chapters_completed' => 0, // Placeholder
        ];
        
        return Inertia::render('Dashboard', [
            'verseOfTheDay' => $randomVerse,
            'lastReading' => $lastReading,
            'readingStats' => $readingStats,
            'userName' => $user->name,
        ]);
    }
}
