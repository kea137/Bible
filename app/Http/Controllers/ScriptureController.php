<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreScriptureRequest;
use App\Http\Requests\UpdateScriptureRequest;
use App\Models\Scripture;

class ScriptureController extends Controller
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
    public function store(StoreScriptureRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Scripture $scripture)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Scripture $scripture)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateScriptureRequest $request, Scripture $scripture)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Scripture $scripture)
    {
        //
    }
}
