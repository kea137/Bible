<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
use App\Models\Bible;
use App\Models\Book;
use App\Models\Chapter;
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

        $bibles = Bible::with('books.chapters')->get();
        // dd($bibles->toArray());

        return Inertia::render('Bibles', [
            'biblesList' => $bibles->toArray(),
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
    public function store(StoreBibleRequest $request)
    {
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
                // Process the JSON data (e.g., create books, chapters, verses)
                // dd($data['BIBLEBOOK'][0]['CHAPTER'][0]['VERSES'][0]['verse_text']);

                foreach ($data['BIBLEBOOK'] as $book) {
                    // Example: Create book, chapters, and verses
                    // $bible->books()->create([...]);
                    $created_book = Book::create([
                        'bible_id' => $bible->id,
                        'book_number' => $book['book_number'],
                        'title' => $book['book_name'],
                        'author' => $book['author'] ?? null,
                        'published_year' => $book['published_year'] ?? null,
                        'introduction' => $book['introduction'] ?? null,
                        'summary' => $book['summary'] ?? null,
                    ]);

                    foreach ($book['CHAPTER'] as $chapter) {
                        // Ensure $chapter is an array before accessing its keys
                        if (!is_array($chapter)) {
                            continue;
                        }

                        $created_chapter = Chapter::create([
                            'bible_id' => $bible->id,
                            'book_id' => $created_book->id, // Adjust as necessary
                            'chapter_number' => $chapter['chapter_number'] ?? 0,
                            'title' => $chapter['title'] ?? null,
                            'introduction' => $chapter['introduction'] ?? null,
                        ]);

                        if (isset($chapter['VERSES']) && is_array($chapter['VERSES'])) {
                            foreach ($chapter['VERSES'] as $verse) {
                                // Create verses
                                if (!is_array($verse)) {
                                    continue;
                                }
                                $created_chapter->verses()->create([
                                    'bible_id' => $bible->id,
                                    'book_id' => $created_book->id,
                                    'chapter_id' => $created_chapter->id,
                                    'verse_number' => $verse['verse_number'],
                                    'text' => $verse['verse_text'],
                                ]);
                            }
                        }
                    }
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
        return response()->json([
            'verses' => $chapter->verses,
        ]);
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
