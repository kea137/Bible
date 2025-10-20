<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
use App\Jobs\BootupBiblesAndReferences;
use App\Models\Bible;
use App\Models\Chapter;
use App\Models\Role;
use App\Services\BibleJsonParser;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
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
        Gate::authorize('create', Role::class);

        return Inertia::render('Create Bible');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBibleRequest $request, BibleJsonParser $parser)
    {

        Gate::authorize('create', Role::class);

        $validated = $request->validated();

        $bible = Bible::create([
            'name' => $validated['name'],
            'abbreviation' => $validated['abbreviation'],
            'language' => $validated['language'],
            'version' => $validated['version'],
            'description' => $validated['description'] ?? null,
        ]);

        // Handle file upload and parsing here
        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // if the file is json, parse it accordingly
            if ($file->getClientOriginalExtension() === 'json') {
                $data = json_decode(file_get_contents($file->getRealPath()), true);

                try {
                    // Use the parser service to handle different JSON formats
                    $parser->parse($bible, $data);
                } catch (\InvalidArgumentException $e) {
                    // If parsing fails, delete the created Bible and return error
                    $bible->delete();

                    return redirect('references')->with('error', 'Failed to parse the uploaded Bible file: '.$e->getMessage());
                }
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
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBibleRequest $request, Bible $bible)
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();

        $bible->update([
            'name' => $validated['name'],
            'abbreviation' => $validated['abbreviation'],
            'language' => $validated['language'],
            'version' => $validated['version'],
            'description' => $validated['description'] ?? null,
        ]);

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

        return redirect()->route('bibles_configure')->with('success', 'Bible and reference installation has been queued. You will be notified when it completes.');
    }

}
