<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class NoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse|Response
    {
        $notes = Note::with(['verse.book', 'verse.chapter'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        if (request()->expectsJson()) {

            return response()->json($notes);
        }

        return Inertia::render('Notes', [
            'notes' => $notes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNoteRequest $request): JsonResponse
    {
        $note = Note::create([
            'user_id' => auth()->id(),
            'verse_id' => $request->verse_id,
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json([
            'success' => true,
            'message' => 'Note saved successfully',
            'note' => $note,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Note $note): JsonResponse
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json($note);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNoteRequest $request, Note $note): JsonResponse
    {
        $note->update([
            'title' => $request->title,
            'content' => $request->content,
        ]);

        $note->load(['verse.book', 'verse.chapter']);

        return response()->json([
            'success' => true,
            'message' => 'Note updated successfully',
            'note' => $note,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Note $note): JsonResponse
    {
        if ($note->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $note->delete();

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
