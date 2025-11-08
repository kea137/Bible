<?php

namespace App\Http\Controllers;

use App\Models\Bible;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OnboardingController extends Controller
{
    /**
     * Show the onboarding page.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        // If onboarding is already completed, redirect to dashboard
        if ($user->onboarding_completed) {
            return redirect()->route('dashboard');
        }

        // Get all available bibles grouped by language
        $bibles = Bible::select('id', 'name', 'abbreviation', 'language', 'version')
            ->orderBy('language')
            ->orderBy('name')
            ->get()
            ->groupBy('language')
            ->map(function ($group) {
                return $group->toArray();
            });

        return Inertia::render('Onboarding', [
            'bibles' => $bibles->toArray(),
            'currentLanguage' => $user->language ?? 'en',
        ]);
    }

    /**
     * Store the user's onboarding preferences.
     */
    public function store(Request $request)
    {
        $request->validate([
            'language' => 'required|string|max:10',
            'preferred_translations' => 'nullable|array',
            'preferred_translations.*' => 'integer|exists:bibles,id',
            'appearance_preferences' => 'nullable|array',
            'appearance_preferences.theme' => 'nullable|string|in:light,dark,system',
        ]);

        $user = $request->user();

        $user->update([
            'language' => $request->language,
            'preferred_translations' => $request->preferred_translations ?? [],
            'appearance_preferences' => $request->appearance_preferences ?? [],
            'onboarding_completed' => true,
        ]);

        return redirect()->route('dashboard')->with('success', 'Welcome! Your preferences have been saved.');
    }

    /**
     * Skip onboarding for now.
     */
    public function skip(Request $request)
    {
        // Allow users to skip onboarding, but they can be prompted again
        return redirect()->route('dashboard');
    }
}
