<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBibleRequest;
use App\Http\Requests\UpdateBibleRequest;
use App\Models\Bible;
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

        return Inertia::render('Bibles', [
            'bibles' => $bibles,
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
    public function store(StoreBibleRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Bible $bible)
    {
        //
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
