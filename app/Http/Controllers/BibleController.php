<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
use App\Jobs\BootupBiblesAndReferences;
use App\Models\Bible;
use App\Models\Chapter;
use App\Models\Role;
use App\Services\BibleJsonParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BibleController extends Controller
{
    public $languageMap = [
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

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only load the necessary fields for the index page to reduce memory usage
        $userPreferences = request()->user()->preferred_translations;

        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version', 'description')
            ->when(!empty($userPreferences) && is_array($userPreferences), function ($q) use ($userPreferences) {
                // Preserve user's preference order first, then fallback to name for others
                $ids = implode(',', array_map('intval', $userPreferences));
                return $q->orderByRaw("(FIELD(id, {$ids}) = 0), FIELD(id, {$ids}), name ASC");
            }, function ($q) {
                return $q->orderBy('name');
            })
            ->get();

        return Inertia::render('Bibles', [
            'biblesList' => $bibles->toArray(),
        ]);
    }

    /**
     * Display the configuration page for managing Bibles.
     */
    public function configure()
    {
        Gate::authorize('create', Role::class);

        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version', 'description')
            ->orderBy('name')
            ->get();

        return Inertia::render('Configure Bibles', [
            'bibles' => $bibles->toArray(),
        ]);
    }

    /**
     * Display parallel bibles view
     */
    public function parallel()
    {
        // Filter bibles based on preffered translations from users onboarding data
        $user = Auth::getUser();
        $bibles_preffered = Bible::select('id', 'name', 'abbreviation', 'language', 'version')
            ->with('books.chapters') // Avoid loading chapters unless needed
            ->whereIn('id', $user->preferred_translations)
            ->get();

        return Inertia::render('Parallel Bibles', [
            'biblesList' => $bibles_preffered->toArray(),
            'biblesOther' => $bibles_preffered->toArray(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bible $bible, Request $request)
    {
        $bible->load('books.chapters');

        if(empty($request->all())){
            $lastReadingProgress = \App\Models\ReadingProgress::where('user_id', Auth::id())
                ->where('completed', true)
                ->with([
                    'bible:id,name',
                    'chapter:id,book_id,chapter_number',
                    'chapter.book:id,title'
                ])
                ->latest('completed_at')
                ->select('id', 'user_id', 'bible_id', 'chapter_id', 'completed_at')
                ->first();

            // Get the last reading point or first book and first chapter 
            $firstBook = $lastReadingProgress && $lastReadingProgress->bible_id === $bible->id
                ? $bible->books->firstWhere('id', $lastReadingProgress->chapter->book_id)
                : $bible->books->first();
            $firstChapter = $lastReadingProgress && $lastReadingProgress->bible_id === $bible->id
                ? $firstBook->chapters->firstWhere('id', $lastReadingProgress->chapter_id)
                : $firstBook->chapters->first();

            if ($firstChapter) {
                $firstChapter->load('verses', 'book');
            }
        } else {
            $firstBook = $bible->books->firstWhere('id', $request->book);
            $firstChapter = $firstBook ? $firstBook->chapters->firstWhere('id', $request->chapter) : null;

            if ($firstChapter) {
                $firstChapter->load('verses', 'book');
            }
        }
        

        return Inertia::render('Bible', [
            'bible' => $bible->toArray(),
            'initialChapter' => $firstChapter ? $firstChapter->toArray() : null,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('Create Bible', [
            'languages' => [
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
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBibleRequest $request, BibleJsonParser $parser)
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();

        // Handle file upload and parsing here
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // if the file is json, parse it accordingly
            if ($file->getClientOriginalExtension() === 'json') {
                $data = json_decode(file_get_contents($file->getRealPath()), true);

                DB::transaction(
                    function() use ($validated, $parser, $data) {
                        try {
                            $bible = Bible::create([
                                'name' => $validated['name'],
                                'abbreviation' => $validated['abbreviation'],
                                'language' => $validated['language'],
                                'version' => $validated['version'],
                                'description' => $validated['description'] ?? null,
                            ]);
                            // Use the parser service to handle different JSON formats
                            $parser->parse($bible, $data);
                        } catch (\InvalidArgumentException $e) {
                            // If parsing fails, delete the created Bible and return error
                            $bible->delete();

                            return redirect('references')->with('error', 'Failed to parse the uploaded Bible file: '.$e->getMessage());
                        }
                    }
                );
                

            }

            // Process the file (e.g., parse and store books, chapters, verses)
            // This is a placeholder for actual file processing logic
        }

        return redirect()->route('bibles')->with('success', 'Bible uploaded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function showChapter(Chapter $chapter)
    {
        $chapter->load('verses', 'book');

        return response()->json($chapter);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bible $bible)
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('Edit Bible', [
            'bible' => $bible->only(['id', 'name', 'abbreviation', 'language', 'version', 'description']),
            'languages' => [
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
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBibleRequest $request, Bible $bible)
    {
        Gate::authorize('create', Role::class);

        DB::transaction(

            function () use ($request, $bible) {

                $validated = $request->validated();

                $bible->update([
                    'name' => $validated['name'],
                    'abbreviation' => $validated['abbreviation'],
                    'language' => $validated['language'],
                    'version' => $validated['version'],
                    'description' => $validated['description'] ?? null,
                ]);
                
            }
        );

        return redirect()->route('bibles_configure')->with('success', 'Bible updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bible $bible)
    {
        Gate::authorize('create', Role::class);

        // Delete the Bible and all associated data (cascading delete)
        $bible->delete();

        return redirect()->route('bibles_configure')->with('success', 'Bible deleted successfully.');
    }

    /**
     * API endpoint to get all bibles
     */
    public function apiBiblesIndex()
    {
        return response()->json(Bible::all());
    }

    /**
     * Boot up all Bibles and References
     * This method dispatches a job to install all Bibles from resources/Bibles
     * and all References for the first Bible from resources/References
     */
    public function bootup()
    {
        Gate::authorize('create', Role::class);

        // Dispatch the job to queue
        BootupBiblesAndReferences::dispatch();

        return redirect()->route('bibles')->with('success', 'Bible and reference installation has been queued. You will be notified when it completes.');
    }

}
