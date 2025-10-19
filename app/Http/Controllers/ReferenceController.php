<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReferenceRequest;
use App\Http\Requests\UpdateReferenceRequest;
use App\Models\Bible;
use App\Models\Reference;
use App\Models\Role;
use App\Models\Verse;
use App\Services\ReferenceService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class ReferenceController extends Controller
{
    public function __construct(private ReferenceService $referenceService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        Gate::authorize('create', Role::class);

        // Get all Bibles with their reference counts
        $bibles = Bible::withCount('references')
            ->select('id', 'name', 'abbreviation', 'language')
            ->orderBy('name')
            ->get()
            ->map(function ($bible) {
                return [
                    'id' => $bible->id,
                    'name' => $bible->name,
                    'abbreviation' => $bible->abbreviation,
                    'language' => $bible->language,
                    'reference_count' => $bible->references_count ?? 0,
                ];
            });

        return Inertia::render('Configure References', [
            'bibles' => $bibles->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Role::class);

        return Inertia::render('Create References', [
            'bibles' => Bible::all()->toArray(),
            'selected_bible' => Bible::first()?->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreReferenceRequest $request)
    {
        Gate::authorize('create', Role::class);

        $validated = $request->validated();
        $bible = Bible::findOrFail($validated['bible_id']);

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            if ($file->getClientOriginalExtension() === 'json') {
                $data = json_decode(file_get_contents($file->getRealPath()), true);
                try {
                    $this->referenceService->loadFromJson($bible, $data);

                    return redirect()->back()->with('success', 'References loaded successfully.');
                } catch (\Exception $e) {
                    return redirect()->back()->with('error', 'Failed to load references: '.$e->getMessage());
                }
            }
        }

        return redirect()->back()->with('error', 'Please upload a valid JSON file.');
    }

    /**
     * Get references for a specific verse (API endpoint)
     */
    public function getVerseReferences(Verse $verse)
    {
        $data = $this->referenceService->getVerseWithReferences($verse->id);

        return response()->json($data);
    }

    /**
     * Show verse study page
     */
    public function studyVerse(Verse $verse)
    {
        return $this->referenceService->studyVerse($verse);
    }

    /**
     * Display the specified resource.
     */
    public function show(Reference $reference)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reference $reference)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateReferenceRequest $request, Reference $reference)
    {
        //
    }

    /**
     * Remove all references for a specific Bible.
     */
    public function destroy(Bible $bible)
    {
        Gate::authorize('create', Role::class);

        // Delete all references for this Bible
        Reference::where('bible_id', $bible->id)->delete();

        return redirect()->route('references_configure')->with('success', 'References deleted successfully.');
    }
}
