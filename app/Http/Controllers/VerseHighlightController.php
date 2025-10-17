<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Models\VerseHighlight;
use Illuminate\Http\Request;

class VerseHighlightController extends Controller
{
    /**
     * Store or update a verse highlight
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'color' => 'required|string|in:yellow,green',
            'note' => 'nullable|string',
        ]);

        $highlight = VerseHighlight::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'verse_id' => $validated['verse_id'],
            ],
            [
                'color' => $validated['color'],
                'note' => $validated['note'] ?? null,
            ]
        );

        return response()->json([
            'success' => true,
            'highlight' => $highlight,
        ]);
    }

    /**
     * Remove a verse highlight
     */
    public function destroy(Verse $verse)
    {
        VerseHighlight::where('user_id', auth()->id())
            ->where('verse_id', $verse->id)
            ->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get all highlights for the authenticated user
     */
    public function index()
    {
        $highlights = VerseHighlight::where('user_id', auth()->id())
            ->with(['verse.book', 'verse.chapter'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($highlights);
    }

    /**
     * Get highlights for a specific chapter
     */
    public function getForChapter(Request $request)
    {
        $validated = $request->validate([
            'chapter_id' => 'required|exists:chapters,id',
        ]);

        $highlights = VerseHighlight::where('user_id', auth()->id())
            ->whereHas('verse', function ($query) use ($validated) {
                $query->where('chapter_id', $validated['chapter_id']);
            })
            ->with('verse')
            ->get()
            ->keyBy('verse_id');

        return response()->json($highlights);
    }
}
