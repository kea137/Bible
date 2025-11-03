<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Http\Requests\UpdateLessonRequest;
use App\Models\Lesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
        return Inertia::render('Create Lesson',[
            'languages' => $this->languages
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
            
                $lesson = Lesson::create([
                    'title'=>$validated['title'],
                    'description'=>$validated['description'],
                    'language'=>$validated['language'],
                    'readable'=>($validated['readable'] === 'False' ? false : true),
                    'no_paragraphs'=>$validated['no_paragraphs'],
                    'user_id'=>Auth::id(),
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
    public function show(Lesson $lesson)
    {
        $lesson->load('paragraphs');
        return Inertia::render('Lesson', [
            'lesson' => $lesson->toArray()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lesson $lesson)
    {
        Gate::authorize('update', Lesson::class);

        return Inertia::render('Edit Lesson', [
            'lesson' => $lesson->with('paragraphs')->find($lesson->id)->toArray(),
            'languages' => $this->languages
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

            function() use ($lesson, $validated) {

                $lesson->paragraphs()->delete();

                foreach($validated['paragraphs'] as $paragraph) {
                    $lesson->paragraphs()->create([
                        'text'=>$paragraph['text']
                    ]);
                }
                
                $lesson->update([
                    'title' => $validated['title'],
                    'description' => $validated['description'],
                    'readable'=>($validated['readable'] === 'False' ? false : true),
                    'language' => $validated['language'],
                    'no_paragraphs' => $validated['no_paragraphs'],
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
}
