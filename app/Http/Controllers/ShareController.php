<?php

namespace App\Http\Controllers;

use App\Models\Verse;
use App\Services\PexelsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ShareController extends Controller
{
    /**
     * Display the verse sharing page.
     */
    public function index(Request $request, PexelsService $pexelsService)
    {
        $verseReference = $request->query('reference', '');
        $verseText = $request->query('text', '');
        $verseId = $request->query('verseId', null);

        // If verseId is provided, validate and fetch the verse from database
        if ($verseId && is_numeric($verseId) && $verseId > 0) {
            $verse = Verse::with(['chapter.book'])->find((int) $verseId);
            if ($verse) {
                $verseReference = $verse->chapter->book->title.' '.$verse->chapter->chapter_number.':'.$verse->verse_number;
                $verseText = $verse->text;
            }
        }

        // Get background images from Pexels
        $backgroundImages = $pexelsService->getBackgroundImages(15);

        return Inertia::render('Share', [
            'verseReference' => $verseReference,
            'verseText' => $verseText,
            'verseId' => ($verseId && is_numeric($verseId)) ? (int) $verseId : null,
            'backgroundImages' => $backgroundImages,
        ]);
    }
}
