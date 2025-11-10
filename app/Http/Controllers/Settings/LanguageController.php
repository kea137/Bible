<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LanguageController extends Controller
{
    /**
     * Update the user's language preference.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => ['required', 'string',
                Rule::in(['en', 'sw', 'fr', 'es', 'de', 'it', 'ru', 'zh', 'ja', 'ar', 'hi', 'bn', 'pa', 'jv', 'ko', 'vi', 'te', 'mr', 'ta'])],
        ]);

        $request->user()->update([
            'language' => $validated['language'],
        ]);

        return back();
    }
}
