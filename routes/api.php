<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MobileApiController;
use App\Http\Controllers\Api\PublicBibleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API Routes
|--------------------------------------------------------------------------
|
| These routes are available to the public with throttling enabled.
| No authentication required.
|
| Standardized URL format for easy remembrance:
| /api/{language}/{version}/{references}/{book}/{chapter}/{verse?}
|
*/

Route::middleware('throttle:30,1')->group(function () {
    // Full path with all parameters
    Route::get('/{language}/{version}/{references}/{book}/{chapter}/{verse?}', [PublicBibleController::class, 'versesPath'])
        ->where([
            'language' => '[a-zA-Z]+',
            'version' => '[a-zA-Z0-9]+',
            'references' => 'true|false|0|1',
            'book' => '[a-zA-Z0-9]+',
            'chapter' => '[0-9]+',
            'verse' => '[0-9]+',
        ]);
});

/*
|--------------------------------------------------------------------------
| Mobile App Authentication Routes
|--------------------------------------------------------------------------
|
| These routes handle authentication for the React Native mobile app.
| Uses Laravel Sanctum for token-based authentication.
|
*/

Route::prefix('mobile/auth')->group(function () {
    // Public authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
    });
});

/*
|--------------------------------------------------------------------------
| Mobile App API Routes
|--------------------------------------------------------------------------
|
| These routes are for the React Native mobile app (kea137/Bible-App).
| Authentication required via Laravel Sanctum.
|
*/

Route::middleware('throttle:30,1')->group(function () {
    // Public routes (no authentication required)
    Route::get('/mobile/home', [MobileApiController::class, 'home']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::get('/mobile/dashboard', [MobileApiController::class, 'dashboard']);

        // Onboarding
        Route::get('/mobile/onboarding', [MobileApiController::class, 'onboarding']);
        Route::post('/mobile/onboarding', [MobileApiController::class, 'storeOnboarding']);

        // Share
        Route::get('/mobile/share', [MobileApiController::class, 'share']);

        // Sitemap
        Route::get('/mobile/sitemap', [MobileApiController::class, 'sitemap']);

        // User preferences
        Route::post('/mobile/update-locale', [MobileApiController::class, 'updateLocale']);
        Route::post('/mobile/update-theme', [MobileApiController::class, 'updateTheme']);
        Route::post('/mobile/update-translations', [MobileApiController::class, 'updateTranslations']);

        // Bibles
        Route::get('/mobile/bibles', [MobileApiController::class, 'bibles']);
        Route::get('/mobile/bibles/parallel', [MobileApiController::class, 'biblesParallel']);
        Route::get('/mobile/bibles/{bible}', [MobileApiController::class, 'bibleShow']);
        Route::get('/mobile/api-bibles', [MobileApiController::class, 'apiBibles']);
        Route::get('/mobile/bibles/chapters/{chapter}', [MobileApiController::class, 'bibleShowChapter']);
        Route::get('/mobile/bibles/{bible}/books/{book}/chapters/{chapter}', [MobileApiController::class, 'bibleShowChapterVerses']);

        // Verse references and study
        Route::get('/mobile/verses/{verse}/references', [MobileApiController::class, 'verseReferences']);
        Route::get('/mobile/verses/{verse}/study', [MobileApiController::class, 'verseStudy']);

        // Verse highlights
        Route::post('/mobile/verse-highlights', [MobileApiController::class, 'verseHighlightsStore']);
        Route::delete('/mobile/verse-highlights/{verse}', [MobileApiController::class, 'verseHighlightsDestroy']);
        Route::get('/mobile/verse-highlights', [MobileApiController::class, 'verseHighlightsIndex']);
        Route::get('/mobile/verse-highlights/chapter', [MobileApiController::class, 'verseHighlightsChapter']);
        Route::get('/mobile/highlighted-verses', [MobileApiController::class, 'highlightedVersesPage']);

        // Notes
        Route::get('/mobile/notes', [MobileApiController::class, 'notes']);
        Route::get('/mobile/notes/index', [MobileApiController::class, 'notesIndex']);
        Route::post('/mobile/notes', [MobileApiController::class, 'notesStore']);
        Route::get('/mobile/notes/{note}', [MobileApiController::class, 'notesShow']);
        Route::put('/mobile/notes/{note}', [MobileApiController::class, 'notesUpdate']);
        Route::delete('/mobile/notes/{note}', [MobileApiController::class, 'notesDestroy']);

        // Reading progress
        Route::get('/mobile/reading-plan', [MobileApiController::class, 'readingPlan']);
        Route::post('/mobile/reading-progress/toggle', [MobileApiController::class, 'readingProgressToggle']);
        Route::get('/mobile/reading-progress/bible', [MobileApiController::class, 'readingProgressBible']);
        Route::get('/mobile/reading-progress/statistics', [MobileApiController::class, 'readingProgressStatistics']);

        // Lessons
        Route::get('/mobile/lessons', [MobileApiController::class, 'lessons']);
        Route::get('/mobile/lessons/{lesson}', [MobileApiController::class, 'showLesson']);
        Route::post('/mobile/lesson-progress/toggle', [MobileApiController::class, 'lessonProgressToggle']);
    });
});

// Sanctum CSRF Cookie Route
Route::prefix('mobile')->get('/sanctum/csrf-cookie', function () {
    $csrfToken = csrf_token();
    return response()->json([
        'message' => 'CSRF cookie set',
        'csrf_token' => $csrfToken,
    ]);
});


