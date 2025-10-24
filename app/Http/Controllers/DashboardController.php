<?php

namespace App\Http\Controllers;

use App\Models\Bible;
use App\Models\Verse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $userLanguage = $request->user()->language;
        $languageMap = [
            'en' => 'English',
            'sw' => 'Swahili',
            'fr' => 'French',
            'es' => 'Spanish',
            'de' => 'German',
            'it' => 'Italian',
            'ru' => 'Russian',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ar' => 'Arabic',
            'hi' => 'Hindi',
            'ko' => 'Korean',
        ];

        $languageName = $languageMap[$userLanguage] ?? 'English';

        // Get total Bibles in user's language
        $totalBibles = Bible::where('language', $languageName)->count();

        // Get last reading from reading progress with only needed fields
        $lastReadingProgress = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->with([
                'bible:id,name',
                'chapter:id,book_id,chapter_number',
                'chapter.book:id,title'
            ])
            ->latest('completed_at')
            ->select('id', 'user_id', 'bible_id', 'chapter_id', 'completed_at')
            ->first();

        // Get random verse of the day with only needed fields
        $randomVerse = Verse::with([
                'bible:id,name,language',
                'book:id,title',
                'chapter:id,chapter_number'
            ])
            ->whereHas('bible', function ($query) use ($languageName) {
                $query->where('language', $languageName);
            })
            ->select('id', 'bible_id', 'book_id', 'chapter_id', 'verse_number', 'text')
            ->inRandomOrder()
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

        // Get chapters read today with only IDs, then sum verses via DB
        $chapterIdsToday = \App\Models\ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->pluck('chapter_id');

        $versesReadToday = 0;
        if ($chapterIdsToday->isNotEmpty()) {
            $versesReadToday = \App\Models\Verse::whereIn('chapter_id', $chapterIdsToday)->count();
        }

        $readingStats = [
            'total_bibles' => $totalBibles,
            'verses_read_today' => $versesReadToday,
            'chapters_completed' => $totalChaptersCompleted,
        ];

        // Only send minimal data to frontend
        return Inertia::render('Dashboard', [
            'verseOfTheDay' => $randomVerse ? [
                'id' => $randomVerse->id,
                'text' => $randomVerse->text,
                'verse_number' => $randomVerse->verse_number,
                'bible' => [
                    'id' => $randomVerse->bible->id,
                    'name' => $randomVerse->bible->name,
                ],
                'book' => [
                    'id' => $randomVerse->book->id,
                    'title' => $randomVerse->book->title,
                ],
                'chapter' => [
                    'id' => $randomVerse->chapter->id,
                    'chapter_number' => $randomVerse->chapter->chapter_number,
                ],
            ] : null,
            'lastReading' => $lastReading,
            'readingStats' => $readingStats,
            'userName' => $user->name,
        ]);
    }

    public function updateLocale(){
        $userId = Auth::id();
        $user = \App\Models\User::find($userId);

        //update users language data
        request()->validate([
            'locale' => 'required|string|max:5',
        ]);

        $locale = request('locale');

        if ($user) {
            $user->language = $locale;
            $user->save();
        }

        return response()->json(['success' => true, 'locale' => $locale]);
    }
}
