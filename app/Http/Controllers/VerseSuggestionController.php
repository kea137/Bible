<?php

namespace App\Http\Controllers;

use App\Models\VerseSuggestion;
use App\Services\VerseSuggestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VerseSuggestionController extends Controller
{
    protected VerseSuggestionService $suggestionService;

    public function __construct(VerseSuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    /**
     * Get suggestions for the authenticated user
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $suggestions = $this->suggestionService->getActiveSuggestions(Auth::user(), $limit);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Generate new suggestions for the authenticated user
     */
    public function generate(Request $request)
    {
        $limit = $request->input('limit', 10);
        $suggestions = $this->suggestionService->generateSuggestionsForUser(Auth::user(), $limit);

        return response()->json([
            'success' => true,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Mark a suggestion as clicked
     */
    public function markClicked(VerseSuggestion $suggestion)
    {
        // Ensure the suggestion belongs to the authenticated user
        if ($suggestion->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->suggestionService->markAsClicked($suggestion);

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Mark a suggestion as dismissed
     */
    public function dismiss(VerseSuggestion $suggestion)
    {
        // Ensure the suggestion belongs to the authenticated user
        if ($suggestion->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $this->suggestionService->markAsDismissed($suggestion);

        return response()->json([
            'success' => true,
        ]);
    }
}
