<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVerseRequest;
use App\Http\Requests\UpdateVerseRequest;
use App\Models\Verse;
use Illuminate\Http\JsonResponse;
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
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVerseRequest $request)
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
    public function update(UpdateVerseRequest $request, Verse $verse) : JsonResponse
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
