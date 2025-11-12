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

Route::prefix('mobile')->group(function () {
    // Public routes (no authentication required)
    Route::get('/home', [MobileApiController::class, 'home']);

    // Authenticated routes
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard
        Route::get('/dashboard', [MobileApiController::class, 'dashboard']);

        // Onboarding
        Route::get('/onboarding', [MobileApiController::class, 'onboarding']);
        Route::post('/onboarding', [MobileApiController::class, 'storeOnboarding']);

        // Share
        Route::get('/share', [MobileApiController::class, 'share']);

        // Sitemap
        Route::get('/sitemap', [MobileApiController::class, 'sitemap']);

        // User preferences
        Route::post('/update-locale', [MobileApiController::class, 'updateLocale']);
        Route::post('/update-theme', [MobileApiController::class, 'updateTheme']);
        Route::post('/update-translations', [MobileApiController::class, 'updateTranslations']);

        // Bibles
        Route::get('/bibles', [MobileApiController::class, 'bibles']);
        Route::get('/bibles/parallel', [MobileApiController::class, 'biblesParallel']);
        Route::get('/bibles/{bible}', [MobileApiController::class, 'bibleShow']);
        Route::get('/api-bibles', [MobileApiController::class, 'apiBibles']);
        Route::get('/bibles/chapters/{chapter}', [MobileApiController::class, 'bibleShowChapter']);

        // Verse references and study
        Route::get('/verses/{verse}/references', [MobileApiController::class, 'verseReferences']);
        Route::get('/verses/{verse}/study', [MobileApiController::class, 'verseStudy']);

        // Verse highlights
        Route::post('/verse-highlights', [MobileApiController::class, 'verseHighlightsStore']);
        Route::delete('/verse-highlights/{verse}', [MobileApiController::class, 'verseHighlightsDestroy']);
        Route::get('/verse-highlights', [MobileApiController::class, 'verseHighlightsIndex']);
        Route::get('/verse-highlights/chapter', [MobileApiController::class, 'verseHighlightsChapter']);
        Route::get('/highlighted-verses', [MobileApiController::class, 'highlightedVersesPage']);

        // Notes
        Route::get('/notes', [MobileApiController::class, 'notes']);
        Route::get('/notes/index', [MobileApiController::class, 'notesIndex']);
        Route::post('/notes', [MobileApiController::class, 'notesStore']);
        Route::get('/notes/{note}', [MobileApiController::class, 'notesShow']);
        Route::put('/notes/{note}', [MobileApiController::class, 'notesUpdate']);
        Route::delete('/notes/{note}', [MobileApiController::class, 'notesDestroy']);

        // Reading progress
        Route::get('/reading-plan', [MobileApiController::class, 'readingPlan']);
        Route::post('/reading-progress/toggle', [MobileApiController::class, 'readingProgressToggle']);
        Route::get('/reading-progress/bible', [MobileApiController::class, 'readingProgressBible']);
        Route::get('/reading-progress/statistics', [MobileApiController::class, 'readingProgressStatistics']);

        // Lessons
        Route::get('/lessons', [MobileApiController::class, 'lessons']);
        Route::get('/lessons/{lesson}', [MobileApiController::class, 'showLesson']);
        Route::post('/lesson-progress/toggle', [MobileApiController::class, 'lessonProgressToggle']);
    });
});
