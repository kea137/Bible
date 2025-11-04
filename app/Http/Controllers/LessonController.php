<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\LessonSeries;
use App\Models\Bible;
use App\Services\ScriptureReferenceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LessonController extends Controller
{
    public $languages = [
        ['id' => 1, 'name' => 'English', 'code' => 'en'],
        ['id' => 2, 'name' => 'Swahili', 'code' => 'sw'],
        ['id' => 3, 'name' => 'French', 'code' => 'fr'],
        ['id' => 4, 'name' => 'Spanish', 'code' => 'es'],
        ['id' => 5, 'name' => 'German', 'code' => 'de'],
        ['id' => 6, 'name' => 'Portuguese', 'code' => 'pt'],
        ['id' => 7, 'name' => 'Italian', 'code' => 'it'],
        ['id' => 8, 'name' => 'Russian', 'code' => 'ru'],
        ['id' => 9, 'name' => 'Chinese', 'code' => 'zh'],
        ['id' => 10, 'name' => 'Japanese', 'code' => 'ja'],
        ['id' => 11, 'name' => 'Arabic', 'code' => 'ar'],
        ['id' => 12, 'name' => 'Hindi', 'code' => 'hi'],
        ['id' => 13, 'name' => 'Bengali', 'code' => 'bn'],
        ['id' => 14, 'name' => 'Punjabi', 'code' => 'pa'],
        ['id' => 15, 'name' => 'Javanese', 'code' => 'jv'],
        ['id' => 16, 'name' => 'Korean', 'code' => 'ko'],
        ['id' => 17, 'name' => 'Vietnamese', 'code' => 'vi'],
        ['id' => 18, 'name' => 'Telugu', 'code' => 'te'],
        ['id' => 19, 'name' => 'Marathi', 'code' => 'mr'],
        ['id' => 20, 'name' => 'Tamil', 'code' => 'ta'],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Lessons', [
            'lessons'=> Lesson::where('readable', true)->get()->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $series = LessonSeries::where('user_id', Auth::id())
            ->orderBy('title')
            ->get(['id', 'title', 'description']);
            
        return Inertia::render('Create Lesson',[
            'languages' => $this->languages,
            'series' => $series->toArray(),
        ]);
    }

    /**
     * Show and manage available resources.
     */
    public function manage()
    {
        $bibles = Lesson::select('id', 'title', 'description', 'language', 'readable')
            ->orderBy('title')
            ->get();

        return Inertia::render('Manage Lessons', [
            'Lessons' => $bibles->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreLessonRequest $request)
    {
        Gate::authorize('create', Lesson::class);

        DB::transaction(

            function () use ($request) {

                $validated = $request->validated();
                
                // Handle series creation or selection
                $seriesId = null;
                $episodeNumber = null;
                
                if ($request->has('new_series_title') && $request->input('new_series_title')) {
                    // Create new series
                    $series = LessonSeries::create([
                        'title' => $request->input('new_series_title'),
                        'description' => $request->input('new_series_description', ''),
                        'language' => $validated['language'],
                        'user_id' => Auth::id(),
                    ]);
                    $seriesId = $series->id;
                    $episodeNumber = $request->input('episode_number', 1);
                } elseif ($request->has('series_id') && $request->input('series_id')) {
                    $seriesId = $request->input('series_id');
                    $episodeNumber = $request->input('episode_number', 1);
                }
            
                $lesson = Lesson::create([
                    'title'=>$validated['title'],
                    'description'=>$validated['description'],
                    'language'=>$validated['language'],
                    'readable'=>($validated['readable'] === 'False' ? false : true),
                    'no_paragraphs'=>$validated['no_paragraphs'],
                    'user_id'=>Auth::id(),
                    'series_id'=>$seriesId,
                    'episode_number'=>$episodeNumber,
                ]);

                foreach($validated['paragraphs'] as $paragraph) {
                    $lesson->paragraphs()->create([
                        'text'=>$paragraph['text']
                    ]);
                }

            }
        );

        return redirect()->route('bibles')->with('success', 'Your Lesson was created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lesson $lesson, ScriptureReferenceService $scriptureService)
    {
        $lesson->load(['paragraphs', 'series']);
        
        // Get the first available Bible for fetching scripture references
        $bible = Bible::first();
        $bibleId = $bible ? $bible->id : null;
        
        // Process description for full verse references ('''BOOK CH:V''')
        $processedDescription = $lesson->description;
        if ($bibleId) {
            $processedDescription = $scriptureService->replaceReferences($lesson->description, $bibleId);
        }
        
        // Parse and fetch scripture references from paragraphs
        $paragraphsWithReferences = $lesson->paragraphs->map(function ($paragraph) use ($scriptureService, $bibleId) {
            $references = $scriptureService->parseReferences($paragraph->text);
            $fetchedReferences = [];
            
            if ($bibleId) {
                foreach ($references as $ref) {
                    $verseData = $scriptureService->fetchVerse($ref['book_code'], $ref['chapter'], $ref['verse'], $bibleId);
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
                ->get(['id', 'title', 'episode_number'])
                ->toArray();
        }
        
        return Inertia::render('Lesson', [
            'lesson' => array_merge($lesson->toArray(), [
                'description' => $processedDescription,
                'paragraphs' => $paragraphsWithReferences,
            ]),
            'userProgress' => $userProgress,
            'seriesLessons' => $seriesLessons,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        Gate::authorize('update', Lesson::class);

        $series = LessonSeries::where('user_id', Auth::id())
            ->orderBy('title')
            ->get(['id', 'title', 'description']);

        return Inertia::render('Edit Lesson', [
            'lesson' => $lesson->with('paragraphs')->find($lesson->id)->toArray(),
            'languages' => $this->languages,
            'series' => $series->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateLessonRequest $request, Lesson $lesson)
    {
        Gate::authorize('update', Lesson::class);

        $validated = $request->validated();

        DB::transaction(

            function() use ($lesson, $validated, $request) {

                $lesson->paragraphs()->delete();

                foreach($validated['paragraphs'] as $paragraph) {
                    $lesson->paragraphs()->create([
                        'text'=>$paragraph['text']
                    ]);
                }
                
                // Handle series creation or selection
                $seriesId = null;
                $episodeNumber = $request->input('episode_number') 
                    ? intval($request->input('episode_number')) 
                    : null;
                
                if ($request->has('new_series_title') && $request->input('new_series_title')) {
                    // Create new series
                    $series = LessonSeries::create([
                        'title' => $request->input('new_series_title'),
                        'description' => $request->input('new_series_description', ''),
                        'language' => $validated['language'],
                        'user_id' => Auth::id(),
                    ]);
                    $seriesId = $series->id;
                } elseif ($request->has('series_id') && $request->input('series_id')) {
                    $seriesId = intval($request->input('series_id'));
                }
                
                $lesson->update([
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'readable'=>($validated['readable'] === 'False' ? false : true),
                    'language' => $validated['language'],
                    'no_paragraphs' => $validated['no_paragraphs'],
                    'series_id' => $seriesId,
                    'episode_number' => $episodeNumber,
                ]);

            }
        );

        return redirect()->route('bibles')->with('success', 'lesson updated successful');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lesson $lesson)
    {
        Gate::authorize('delete', Lesson::class);

        $lesson->delete();

        return redirect()->route('bibles')->with('success', 'Lesson deleted successfully');
    }

    /**
     * Toggle lesson completion for the authenticated user
     */
    public function toggleProgress(Request $request)
    {
        $validated = $request->validate([
            'lesson_id' => 'required|exists:lessons,id',
        ]);

        $progress = LessonProgress::where('user_id', Auth::id())
            ->where('lesson_id', $validated['lesson_id'])
            ->first();

        if ($progress) {
            $progress->completed = !$progress->completed;
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
            'progress' => $progress,
        ]);
    }
}
