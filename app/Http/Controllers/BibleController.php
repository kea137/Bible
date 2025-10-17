<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
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
        $bibles = Bible::all();

        if ($bibles->count() === 0) {
            // If no bibles exist, create a default one
            $bible = Bible::create([
                'name' => 'Default Bible',
                'abbreviation' => 'KJV',
                'description' => 'This is the default Bible.',
                'language' => 'English',
                'version' => 'KJV 1611',
            ]);

            // Optionally, you can add default books, chapters, and verses here

            $bibles = Bible::all(); // Refresh the list of bibles
        }

        // Get user's preferred language from cookie or default to 'en'
        $userLanguage = request()->cookie('language', 'en');
        $languageMap = [
            'en' => 'English',
            'sw' => 'Swahili',
        ];
        
        $languageName = $languageMap[$userLanguage] ?? 'English';
        
        // Filter bibles by language
        $bibles = Bible::with('books.chapters')
            ->where('language', $languageName)
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
            'abbreviation' => strtoupper(substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, 9)), // to be changed later
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

                    return redirect('references')->with('error', 'Failed to parse the uploaded Bible file: ' . $e->getMessage());
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
}
