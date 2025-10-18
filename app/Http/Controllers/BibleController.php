<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
use App\Jobs\ProcessBibleUpload;
use App\Models\Bible;
use App\Models\Chapter;
use App\Models\Role;
use App\Services\BibleJsonParser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class BibleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Only load the necessary fields for the index page to reduce memory usage
        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version', 'description')
            ->orderBy('name')
            ->get();

        return Inertia::render('Bibles', [
            'biblesList' => $bibles->toArray(),
        ]);
    }

    /**
     * Display parallel bibles view
     */
    public function parallel()
    {
        // Get user's preferred language from cookie or default to 'en'
        $userLanguage = request()->user()->language ?? 'en';
        $languageMap = [
            'en' => 'English',
            'sw' => 'Swahili',
        ];

        $languageName = $languageMap[$userLanguage] ?? 'English';

        // Filter bibles by language
        $bibles_preffered = Bible::with('books.chapters')
            ->where('language', $languageName)
            ->get();
        $bibles_other = Bible::with('books.chapters')
            ->get();

        return Inertia::render('Parallel Bibles', [
            'biblesList' => $bibles_preffered->toArray(),
            'biblesOther' => $bibles_other->toArray(),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bible $bible)
    {
        $bible->load('books.chapters');

        // Get the first book and first chapter
        $firstBook = $bible->books->first();
        $firstChapter = $firstBook ? $firstBook->chapters->first() : null;

        if ($firstChapter) {
            $firstChapter->load('verses', 'book');
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
        Gate::authorize('create', Bible::class);

        return Inertia::render('Create Bible');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBibleRequest $request, BibleJsonParser $parser)
    {

        Gate::authorize('create', Bible::class);

        $validated = $request->validated();

        $bible = Bible::create([
            'name' => $validated['name'],
            'abbreviation' => $validated['abbreviation'],
            'language' => $validated['language'],
            'version' => $validated['version'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending',
        ]);

        // Handle file upload and parsing here
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // if the file is json, parse it accordingly
            if ($file->getClientOriginalExtension() === 'json') {
                $data = json_decode(file_get_contents($file->getRealPath()), true);

                // Validate the JSON structure before dispatching the job
                try {
                    // Quick validation by trying to detect format
                    $reflection = new \ReflectionClass($parser);
                    $method = $reflection->getMethod('detectFormat');
                    $method->setAccessible(true);
                    $method->invoke($parser, $data);
                } catch (\InvalidArgumentException $e) {
                    // If format detection fails, delete the created Bible and return error
                    $bible->delete();

                    return redirect()->back()->withErrors([
                        'file' => 'Failed to parse the uploaded Bible file: '.$e->getMessage(),
                    ])->withInput();
                }

                // Dispatch the job to process the Bible upload asynchronously
                ProcessBibleUpload::dispatch($bible, $data, $request->user()->id);

                return redirect()->route('bibles')->with([
                    'info' => 'Bible upload started. You will be notified when it completes.',
                    'bible_id' => $bible->id,
                ]);
            }

            // Process the file (e.g., parse and store books, chapters, verses)
            // This is a placeholder for actual file processing logic
        }

        return redirect()->route('bibles')->with('success', 'Bible created successfully.');
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
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBibleRequest $request, Bible $bible)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bible $bible)
    {
        //
    }

    /**
     * API endpoint to get all bibles
     */
    public function apiBiblesIndex()
    {
        return response()->json(Bible::all());
    }

    /**
     * API endpoint to get Bible status
     */
    public function getBibleStatus(Bible $bible)
    {
        return response()->json([
            'id' => $bible->id,
            'name' => $bible->name,
            'status' => $bible->status,
            'error_message' => $bible->error_message,
        ]);
    }
}
