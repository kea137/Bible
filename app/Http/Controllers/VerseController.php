<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateVerseRequest;
use App\Models\Verse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class VerseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Search verses using Laravel Scout (Algolia/Meilisearch/Collection driver)
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'query' => 'nullable|string|max:255',
            'limit' => 'nullable|integer|min:1|max:50',
        ]);

        $query = $validated['query'] ?? '';
        $limit = $validated['limit'] ?? 10;

        if (empty($query)) {
            return response()->json([
                'verses' => [],
                'total' => 0,
            ]);
        }

        // Use Laravel Scout to search verses
        $verses = Verse::search($query)
            ->query(function ($builder) {
                $builder->with(['book:id,title', 'chapter:id,chapter_number']);
            })
            ->take($limit)
            ->get();

        return response()->json([
            'verses' => $verses->map(function ($verse) {
                return [
                    'id' => $verse->id,
                    'text' => $verse->text,
                    'verse_number' => $verse->verse_number,
                    'bible_id' => $verse->bible_id,
                    'book' => [
                        'id' => $verse->book->id ?? null,
                        'title' => $verse->book->title ?? '',
                    ],
                    'chapter' => [
                        'id' => $verse->chapter->id ?? null,
                        'chapter_number' => $verse->chapter->chapter_number ?? null,
                    ],
                ];
            }),
            'total' => $verses->count(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Verse $verse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Verse $verse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVerseRequest $request, Verse $verse): JsonResponse
    {
        Gate::authorize('update', Verse::class);

        $request->validated;

        Verse::where('id', $request->verse_id)->update([
            'text' => $request->text,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Verse updated successfully',
            'verse' => $verse,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Verse $verse)
    {
        //
    }
}
