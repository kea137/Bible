<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\Note;
use App\Models\ReadingProgress;
use App\Models\Verse;
use App\Models\VerseHighlight;
use App\Services\PexelsService;
use App\Services\ReferenceService;
use App\Services\ScriptureReferenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileApiController extends Controller
{
    public function __construct(
        private ReferenceService $referenceService,
        private ScriptureReferenceService $scriptureService
    ) {}

    /**
     * Get home page data
     */
    public function home(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Welcome to Bible API',
            'version' => '1.0.0',
        ]);
    }

    /**
     * Get dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get user's preferred language
        $userLanguage = $user->language;
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

        // Get last reading from reading progress
        $lastReadingProgress = ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->with([
                'bible:id,name',
                'chapter:id,book_id,chapter_number',
                'chapter.book:id,title',
            ])
            ->latest('completed_at')
            ->select('id', 'user_id', 'bible_id', 'chapter_id', 'completed_at')
            ->first();

        // Get random verse of the day
        $randomVerse = Verse::with([
            'bible:id,name,language',
            'book:id,title',
            'chapter:id,chapter_number',
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
        $totalChaptersCompleted = ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->count();

        // Get chapters read today
        $chapterIdsToday = ReadingProgress::where('user_id', $user->id)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->pluck('chapter_id');

        $versesReadToday = 0;
        if ($chapterIdsToday->isNotEmpty()) {
            $versesReadToday = Verse::whereIn('chapter_id', $chapterIdsToday)->count();
        }

        $highlights = VerseHighlight::where('user_id', Auth::id())
            ->with(['verse.book', 'verse.chapter'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'highlightedVerses' => $highlights,
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
                'readingStats' => [
                    'total_bibles' => $totalBibles,
                    'verses_read_today' => $versesReadToday,
                    'chapters_completed' => $totalChaptersCompleted,
                ],
                'userName' => $user->name,
            ],
        ]);
    }

    /**
     * Get onboarding data
     */
    public function onboarding(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get all available bibles grouped by language
        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version')
            ->orderBy('language')
            ->orderBy('name')
            ->get()
            ->groupBy('language')
            ->map(function ($group) {
                return $group->toArray();
            });

        return response()->json([
            'success' => true,
            'data' => [
                'bibles' => $bibles->toArray(),
                'currentLanguage' => $user->language ?? 'en',
                'onboarding_completed' => $user->onboarding_completed,
            ],
        ]);
    }

    /**
     * Store onboarding preferences
     */
    public function storeOnboarding(Request $request): JsonResponse
    {
        $request->validate([
            'language' => 'required|string|max:10',
            'preferred_translations' => 'nullable|array',
            'preferred_translations.*' => 'integer|exists:bibles,id',
            'appearance_preferences' => 'nullable|array',
            'appearance_preferences.theme' => 'nullable|string|in:light,dark,system',
        ]);

        $user = $request->user();

        $user->update([
            'language' => $request->language,
            'preferred_translations' => $request->preferred_translations ?? [],
            'appearance_preferences' => $request->appearance_preferences ?? [],
            'onboarding_completed' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'data' => [
                'user' => $user,
            ],
        ]);
    }

    /**
     * Get share page data
     */
    public function share(Request $request, PexelsService $pexelsService): JsonResponse
    {
        $verseReference = $request->query('reference', '');
        $verseText = $request->query('text', '');
        $verseId = $request->query('verseId', null);
        $bible = null;
        $book = null;
        $chapter = null;

        // If verseId is provided, validate and fetch the verse from database
        if ($verseId && is_numeric($verseId) && $verseId > 0) {
            $verse = Verse::with(['chapter.book'])->find((int) $verseId);
            if ($verse) {
                $verseReference = $verse->chapter->book->title.' '.$verse->chapter->chapter_number.':'.$verse->verse_number;
                $verseText = $verse->text;
                $bible = $verse->chapter->book->bible_id;
                $book = $verse->chapter->book->id;
                $chapter = $verse->chapter->id;
            }
        }

        // Get background images from Pexels
        $backgroundImages = $pexelsService->getBackgroundImages(15);

        return response()->json([
            'success' => true,
            'data' => [
                'verseReference' => $verseReference,
                'verseText' => $verseText,
                'verseId' => ($verseId && is_numeric($verseId)) ? (int) $verseId : null,
                'backgroundImages' => $backgroundImages,
                'bible' => $bible,
                'book' => $book,
                'chapter' => $chapter,
            ],
        ]);
    }

    /**
     * Get sitemap data
     */
    public function sitemap(): JsonResponse
    {
        $bibles = Bible::select('id', 'name', 'abbreviation')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'bibles' => $bibles,
            ],
        ]);
    }

    /**
     * Update user locale
     */
    public function updateLocale(Request $request): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:5',
        ]);

        $user = $request->user();
        $user->language = $request['locale'];
        $user->save();

        return response()->json([
            'success' => true,
            'locale' => $request['locale'],
        ]);
    }

    /**
     * Update user theme
     */
    public function updateTheme(Request $request): JsonResponse
    {
        $request->validate([
            'theme' => 'required|string|in:light,dark,system',
        ]);

        $user = $request->user();
        $preferences = $user->appearance_preferences ?? [];
        $preferences['theme'] = $request->theme;
        $user->appearance_preferences = $preferences;
        $user->save();

        return response()->json([
            'success' => true,
            'theme' => $request->theme,
        ]);
    }

    /**
     * Update preferred translations
     */
    public function updateTranslations(Request $request): JsonResponse
    {
        $request->validate([
            'preferred_translations' => 'nullable|array',
            'preferred_translations.*' => 'integer|exists:bibles,id',
        ]);

        $user = $request->user();
        $user->preferred_translations = $request->preferred_translations ?? [];
        $user->save();

        return response()->json([
            'success' => true,
            'translations' => $request->preferred_translations ?? [],
        ]);
    }

    /**
     * Get list of bibles
     */
    public function bibles(Request $request): JsonResponse
    {
        $userPreferences = $request->user()->preferred_translations;

        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version', 'description')
            ->when(! empty($userPreferences) && is_array($userPreferences), function ($q) use ($userPreferences) {
                $ids = implode(',', array_map('intval', $userPreferences));

                return $q->orderByRaw("(FIELD(id, {$ids}) = 0), FIELD(id, {$ids}), name ASC");
            }, function ($q) {
                return $q->orderBy('name');
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bibles,
        ]);
    }


    /**
     * Mark as read function for chapters
     */
    public function markAsRead(Chapter $chapter){
        $userId = Auth::id();

        $progress = ReadingProgress::where('user_id', $userId)
            ->where('chapter_id', $chapter->id)
            ->first();

        if ($progress) {
            if (! $progress->completed) {
                $progress->completed = true;
                $progress->completed_at = now();
                $progress->save();
            }
        } else {
            $progress = ReadingProgress::create([
                'user_id' => $userId,
                'bible_id' => $chapter->bible_id,
                'chapter_id' => $chapter->id,
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Get parallel bibles
     */
    public function biblesParallel(Request $request): JsonResponse
    {
        $user = $request->user();
        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version')
            ->with('books.chapters')
            ->whereIn('id', $user->preferred_translations)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bibles,
        ]);
    }

    public function bibleShowChapterVerses(Bible $bible, Book $book, Chapter $chapter = null): JsonResponse
    {
        if (! $chapter) {
            $chapter_load = Chapter::where('bible_id', $bible->id)
                ->where('book_id', $book->id)
                ->with(['verses' => function ($query) {
                    $query->with(['highlight' => function ($q) {
                        $q->where('user_id', Auth::id())
                            ->select('id', 'verse_id', 'color');
                    }]);
                }])
                ->first();
                
        } else {
            $chapter_load = $chapter->load(['verses' => function ($query) {
                $query->with(['highlight' => function ($q) {
                    $q->where('user_id', Auth::id())
                        ->select('id', 'verse_id', 'color');
                }]);
            }]);

        // Check if chapter is read
        $isRead = ReadingProgress::where('user_id', Auth::id())
            ->where('chapter_id', $chapter_load->id)
            ->where('completed', true)
            ->exists();

        // Pass book data with chapters_count
        $book_data = $book->toArray();
        $book_data['chapters_count'] = $book->chapters()->count();

        if ($chapter_load) {
            $chapter_load->book = $book_data;
            $chapter_load->is_read = $isRead;
        }

        return response()->json([
            'success' => true,
            'data' => $chapter_load,
        ]);
    }

    /**
     * Get specific bible
     */
    public function bibleShow(Bible $bible, Request $request): JsonResponse
    {
        $bible->load(['books' => function ($query) {
            $query->select('id', 'bible_id', 'title', 'book_number', 'author')
                ->with(['chapters' => function ($q) {
                    $q->select('id', 'book_id', 'chapter_number');
                }]);
        }]);

        // Add chapters_count to each book
        foreach ($bible->books as $book) {
            $book->chapters_count = $book->chapters->count();
        }

        // Get read chapters for the current user
        $readChapters = ReadingProgress::where('user_id', Auth::id())
            ->where('bible_id', $bible->id)
            ->where('completed', true)
            ->pluck('chapter_id')
            ->toArray();

        // Add read status to each chapter
        foreach ($bible->books as $book) {
            foreach ($book->chapters as $chapter) {
                $chapter->is_read = in_array($chapter->id, $readChapters);
            }
        }

        $firstChapter = null;

        if (empty($request->all())) {
            $lastReadingProgress = ReadingProgress::where('user_id', Auth::id())
                ->where('completed', true)
                ->with([
                    'bible:id,name',
                    'chapter:id,book_id,chapter_number',
                    'chapter.book:id,title',
                ])
                ->latest('completed_at')
                ->select('id', 'user_id', 'bible_id', 'chapter_id', 'completed_at')
                ->first();

            $firstBook = $lastReadingProgress && $lastReadingProgress->bible_id === $bible->id
                ? $bible->books->firstWhere('id', $lastReadingProgress->chapter->book_id)
                : $bible->books->first();
            $firstChapter = $lastReadingProgress && $lastReadingProgress->bible_id === $bible->id && $firstBook
                ? $firstBook->chapters->firstWhere('id', $lastReadingProgress->chapter_id)
                : ($firstBook ? $firstBook->chapters->first() : null);

            if ($firstChapter) {
                $firstChapter->load(['verses' => function ($query) {
                    $query->with(['highlight' => function ($q) {
                        $q->where('user_id', Auth::id())
                            ->select('id', 'verse_id', 'color');
                    }]);
                }, 'book']);
            }
        } else {
            $firstBook = $bible->books->firstWhere('id', $request->book);
            $firstChapter = $firstBook ? $firstBook->chapters->firstWhere('id', $request->chapter) : null;

            if ($firstChapter) {
                $firstChapter->load(['verses' => function ($query) {
                    $query->with(['highlight' => function ($q) {
                        $q->where('user_id', Auth::id())
                            ->select('id', 'verse_id', 'color');
                    }]);
                }, 'book']);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'bible' => $bible,
                'initialChapter' => $firstChapter,
                'readChapters' => $readChapters,
            ],
        ]);
    }

    /**
     * Get all bibles (API endpoint)
     */
    public function apiBibles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Bible::all(),
        ]);
    }

    /**
     * Get chapter with verses
     */
    public function bibleShowChapter(Chapter $chapter): JsonResponse
    {
        $chapter->load(['verses' => function ($query) {
            $query->with(['highlight' => function ($q) {
                $q->where('user_id', Auth::id())
                    ->select('id', 'verse_id', 'color');
            }]);
        }, 'book']);
        
        $isRead = ReadingProgress::where('user_id', Auth::id())
            ->where('chapter_id', $chapter->id)
            ->where('completed', true)
            ->exists();
        
        $chapter['is_read'] = $isRead;

        return response()->json([
            'success' => true,
            'data' => $chapter,
        ]);
    }

    /**
     * Get verse references
     */
    public function verseReferences(Verse $verse): JsonResponse
    {
        $data = $this->referenceService->getVerseWithReferences($verse->id);

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get verse study data
     */
    public function verseStudy(Verse $verse): JsonResponse
    {
        $data = $this->referenceService->getVerseWithReferences($verse->id);

        // Get other Bible versions of the same verse
        // Get user's preferred Bible IDs (assume from authenticated user or passed in)
        $user = Auth::user();

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

        $data['other_translations'] = $otherVersions;

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store verse highlight
     */
    public function verseHighlightsStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'color' => 'required|string|in:yellow,green',
            'note' => 'nullable|string',
        ]);

        $highlight = VerseHighlight::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'verse_id' => $validated['verse_id'],
            ],
            [
                'color' => $validated['color'],
                'note' => $validated['note'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $highlight,
        ]);
    }

    /**
     * Delete verse highlight
     */
    public function verseHighlightsDestroy(Verse $verse): JsonResponse
    {
        VerseHighlight::where('user_id', Auth::id())
            ->where('verse_id', $verse->id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Highlight deleted successfully',
        ]);
    }

    /**
     * Get all highlights
     */
    public function verseHighlightsIndex(): JsonResponse
    {
        $highlights = VerseHighlight::where('user_id', Auth::id())
            ->with(['verse.book', 'verse.chapter'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $highlights,
        ]);
    }

    /**
     * Get highlights for a specific chapter
     */
    public function verseHighlightsChapter(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $highlights = VerseHighlight::where('user_id', Auth::id())
            ->whereHas('verse', function ($query) use ($validated) {
                $query->where('chapter_id', $validated['chapter_id']);
            })
            ->with('verse')
            ->get()
            ->keyBy('verse_id');

        return response()->json([
            'success' => true,
            'data' => $highlights,
        ]);
    }

    /**
     * Get highlighted verses page data
     */
    public function highlightedVersesPage(): JsonResponse
    {
        $highlights = VerseHighlight::where('user_id', Auth::id())
            ->with(['verse.book', 'verse.chapter', 'verse.bible'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $highlights,
        ]);
    }

    /**
     * Get notes page data
     */
    public function notes(): JsonResponse
    {
        $notes = Note::with(['verse.book', 'verse.chapter'])
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notes,
        ]);
    }

    /**
     * Get all notes
     */
    public function notesIndex(): JsonResponse
    {
        return $this->notes();
    }

    /**
     * Store a new note
     */
    public function notesStore(StoreNoteRequest $request): JsonResponse
    {
        $note = Note::create([
            'user_id' => Auth::id(),
            'verse_id' => $request['verse_id'],
            'title' => $request['title'],
            'content' => $request['content'],
        ]);

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully',
            'data' => $note,
        ], 201);
    }

    /**
     * Get a single note
     */
    public function notesShow(Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json([
            'success' => true,
            'data' => $note,
        ]);
    }

    /**
     * Update a note
     */
    public function notesUpdate(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->update([
            'title' => $request['title'],
            'content' => $request['content'],
        ]);

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'data' => $note,
        ]);
    }

    /**
     * Delete a note
     */
    public function notesDestroy(Note $note): JsonResponse
    {
        if ($note->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }

    /**
     * Get reading plan
     */
    public function readingPlan(Request $request): JsonResponse
    {
        $userId = Auth::id();

        $selectedBibleId = $request->input('bible_id');

        if (! $selectedBibleId) {
            $lastReading = ReadingProgress::where('user_id', $userId)
                ->where('completed', true)
                ->latest('completed_at')
                ->first();

            $selectedBibleId = $lastReading ? $lastReading->bible_id : Bible::orderBy('id')->value('id');
        }

        $selectedBible = Bible::find($selectedBibleId);
        $allBibles = Bible::all();

        $totalChapters = Chapter::whereHas('book', function ($query) use ($selectedBibleId) {
            $query->where('bible_id', $selectedBibleId);
        })->count();

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

        $completedLessons = LessonProgress::where('user_id', $userId)
            ->where('completed', true)
            ->with('lesson.series')
            ->get();

        $lessonsReadToday = LessonProgress::where('user_id', $userId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->count();

        return response()->json([
            'success' => true,
            'data' => [
                'totalChapters' => $totalChapters,
                'completedChapters' => $completedChapters,
                'progressPercentage' => $progressPercentage,
                'chaptersReadToday' => $chaptersReadToday,
                'selectedBible' => $selectedBible,
                'allBibles' => $allBibles,
                'completedLessons' => $completedLessons,
                'lessonsReadToday' => $lessonsReadToday,
            ],
        ]);
    }

    /**
     * Toggle chapter completion
     */
    public function readingProgressToggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
            'bible_id' => 'required|exists:bibles,id',
        ]);

        $progress = ReadingProgress::where('user_id', Auth::id())
            ->where('chapter_id', $validated['chapter_id'])
            ->first();

        if ($progress) {
            $progress->completed = ! $progress->completed;
            $progress->completed_at = $progress->completed ? now() : null;
            $progress->save();
        } else {
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
            'data' => $progress,
        ]);
    }

    /**
     * Get bible reading progress
     */
    public function readingProgressBible(Request $request): JsonResponse
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

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }

    /**
     * Get reading statistics
     */
    public function readingProgressStatistics(): JsonResponse
    {
        $userId = Auth::id();

        $totalChaptersCompleted = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->count();

        $chaptersReadToday = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->whereDate('completed_at', today())
            ->with('chapter.verses')
            ->get();

        $versesReadToday = $chaptersReadToday->sum(function ($progress) {
            return $progress->chapter->verses->count();
        });

        $lastReading = ReadingProgress::where('user_id', $userId)
            ->where('completed', true)
            ->with(['bible', 'chapter.book'])
            ->latest('completed_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'total_chapters_completed' => $totalChaptersCompleted,
                'chapters_read_today' => $chaptersReadToday->count(),
                'verses_read_today' => $versesReadToday,
                'last_reading' => $lastReading ? [
                    'bible_id' => $lastReading->bible_id,
                    'bible_name' => $lastReading->bible->name,
                    'book_title' => $lastReading->chapter->book->title,
                    'chapter_number' => $lastReading->chapter->chapter_number,
                ] : null,
            ],
        ]);
    }

    /**
     * Get lessons
     */
    public function lessons(): JsonResponse
    {
        $lessons = Lesson::where('readable', true)->get();

        return response()->json([
            'success' => true,
            'data' => $lessons,
        ]);
    }

    /**
     * Get lesson details
     */
    public function showLesson(Lesson $lesson): JsonResponse
    {
        $lesson->load(['paragraphs', 'series']);

        // Get the first available Bible for fetching scripture references
        $bible = Bible::first();
        $bibleId = $bible ? $bible->id : null;

        // Process description for full verse references
        $processedDescription = $lesson->description;
        if ($bibleId) {
            $processedDescription = $this->scriptureService->replaceReferences($lesson->description, $bibleId);
        }

        // Parse and fetch scripture references from paragraphs
        $paragraphsWithReferences = $lesson->paragraphs->map(function ($paragraph) use ($bibleId) {
            $references = $this->scriptureService->parseReferences($paragraph->text);
            $fetchedReferences = [];

            if ($bibleId) {
                foreach ($references as $ref) {
                    $verseData = $this->scriptureService->fetchVerse($ref['book_code'], $ref['chapter'], $ref['verse'], $bibleId);
                    if ($verseData) {
                        $fetchedReferences[] = array_merge($ref, $verseData);
                    }
                }
            }

            return [
                'id' => $paragraph->id,
                'title' => $paragraph->title,
                'text' => $paragraph->text,
                'references' => $fetchedReferences,
            ];
        });

        // Get user's progress for this lesson
        $userProgress = null;
        if (Auth::check()) {
            $userProgress = LessonProgress::where('user_id', Auth::id())
                ->where('lesson_id', $lesson->id)
                ->first();
        }

        // Get series lessons if this lesson is part of a series
        $seriesLessons = [];
        if ($lesson->series_id) {
            $seriesLessons = Lesson::where('series_id', $lesson->series_id)
                ->orderBy('episode_number')
                ->get(['id', 'title', 'episode_number']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'lesson' => array_merge($lesson->toArray(), [
                    'description' => $processedDescription,
                    'paragraphs' => $paragraphsWithReferences,
                ]),
                'userProgress' => $userProgress,
                'seriesLessons' => $seriesLessons,
            ],
        ]);
    }

    /**
     * Toggle lesson progress
     */
    public function lessonProgressToggle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $progress = LessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $validated['lesson_id'])
            ->first();

        if ($progress) {
            $progress->completed = ! $progress->completed;
            $progress->completed_at = $progress->completed ? now() : null;
            $progress->save();
        } else {
            $progress = LessonProgress::create([
                'user_id' => Auth::id(),
                'lesson_id' => $validated['lesson_id'],
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }
}
