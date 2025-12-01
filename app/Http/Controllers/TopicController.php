<?php

namespace App\Http\Controllers;

use App\Models\Topic;
use App\Models\Verse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TopicController extends Controller
{
    /**
     * Display a listing of topics
     */
    public function index()
    {
        $topics = Topic::where('is_active', true)
            ->orderBy('order')
            ->orderBy('title')
            ->withCount('verses')
            ->get()
            ->groupBy('category');

        return Inertia::render('Topics/Index', [
            'topics' => $topics,
        ]);
    }

    /**
     * Display a specific topic with its verse chain
     */
    public function show(Topic $topic)
    {
        $topic->load([
            'verses.book',
            'verses.chapter',
            'verses.bible',
        ]);

        return Inertia::render('Topics/Show', [
            'topic' => $topic,
        ]);
    }

    /**
     * Store a new topic
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $topic = Topic::create($validated);

        return response()->json([
            'success' => true,
            'topic' => $topic,
        ]);
    }

    /**
     * Update a topic
     */
    public function update(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'title' => 'string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:255',
            'order' => 'integer',
            'is_active' => 'boolean',
        ]);

        $topic->update($validated);

        return response()->json([
            'success' => true,
            'topic' => $topic,
        ]);
    }

    /**
     * Delete a topic
     */
    public function destroy(Topic $topic)
    {
        $topic->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Add a verse to a topic
     */
    public function addVerse(Request $request, Topic $topic)
    {
        $validated = $request->validate([
            'verse_id' => 'required|exists:verses,id',
            'order' => 'integer',
            'note' => 'nullable|string',
        ]);

        $topic->verses()->attach($validated['verse_id'], [
            'order' => $validated['order'] ?? $topic->verses()->count(),
            'note' => $validated['note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Remove a verse from a topic
     */
    public function removeVerse(Topic $topic, Verse $verse)
    {
        $topic->verses()->detach($verse->id);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Update verse order or note in a topic
     */
    public function updateVerse(Request $request, Topic $topic, Verse $verse)
    {
        $validated = $request->validate([
            'order' => 'nullable|integer',
            'note' => 'nullable|string',
        ]);

        $topic->verses()->updateExistingPivot($verse->id, $validated);

        return response()->json([
            'success' => true,
        ]);
    }
}
