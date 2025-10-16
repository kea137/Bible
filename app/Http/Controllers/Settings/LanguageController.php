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
            'language' => ['required', 'string', Rule::in(['en', 'sw'])],
        ]);

        $request->user()->update([
            'language' => $validated['language'],
        ]);

        return back();
    }
}
