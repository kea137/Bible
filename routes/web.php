<?php

use App\Http\Controllers\BibleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VerseController;
use App\Http\Controllers\VerseHighlightController;
use App\Http\Controllers\VerseLinkController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/license', function () {
    return Inertia::render('License');
})->name('license');

Route::get('/documentation', function () {
    return Inertia::render('Documentation');
})->name('documentation');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'check.onboarding'])->name('dashboard');

// Onboarding routes
Route::middleware(['auth'])->group(function () {
    Route::get('onboarding', [OnboardingController::class, 'show'])->name('onboarding');
    Route::post('onboarding', [OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('onboarding/skip', [OnboardingController::class, 'skip'])->name('onboarding.skip');
});

// Share verse routes
Route::get('/share', [ShareController::class, 'index'])->name('share');

// Sitemap for SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// Store user's locale preference
Route::post('/api/user/locale', [DashboardController::class, 'updateLocale'])->name('update_locale')->middleware('auth');

// Store user's theme preference
Route::post('/api/user/theme', [DashboardController::class, 'updateTheme'])->name('update_theme')->middleware('auth');

// Store user's preferred translations
Route::post('/api/user/translations', [DashboardController::class, 'updateTranslations'])->name('update_translations')->middleware('auth');

// Store user's font preferences
Route::post('/api/user/font-preferences', [DashboardController::class, 'updateFontPreferences'])->name('update_font_preferences')->middleware('auth');

Route::get('/bibles', [BibleController::class, 'index'])->name('bibles')->middleware(['auth', 'check.onboarding']);
Route::get('/bibles/parallel', [BibleController::class, 'parallel'])->name('bibles_parallel')->middleware(['auth', 'check.onboarding']);
Route::get('/bibles/configure', [BibleController::class, 'configure'])->name('bibles_configure')->middleware(['auth', 'check.onboarding', 'can:update,App\\Models\\Bible']);
Route::post('/bibles/bootup', [BibleController::class, 'bootup'])->name('bibles_bootup')->middleware(['auth', 'check.onboarding', 'can:update,App\\Models\\Bible']);
Route::get('/bibles/{bible}', [BibleController::class, 'show'])->name('bible_show')->middleware(['auth', 'check.onboarding']);
Route::get('/bibles/upload/bible', [BibleController::class, 'create'])->name('bible_create')->middleware(['auth', 'check.onboarding', 'can:update,App\\Models\\Bible']);
Route::post('/bibles/create/bible', [BibleController::class, 'store'])->name('bible_store')->middleware(['auth', 'check.onboarding', 'can:update,App\\Models\\Bible']);
Route::get('/bibles/{bible}/edit', [BibleController::class, 'edit'])->name('bible_edit')->middleware(['auth', 'check.onboarding', 'can:update,App\\Models\\Bible']);
Route::put('/bibles/{bible}', [BibleController::class, 'update'])->name('bible_update')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::delete('/bibles/{bible}', [BibleController::class, 'destroy'])->name('bible_destroy')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::get('/api/bibles', [BibleController::class, 'apiBiblesIndex'])->name('api_bibles')->middleware('auth');
Route::get('/api/bibles/books/chapters/{chapter}', [BibleController::class, 'showChapter'])->name('bible_show_chapter');

// Role Management routes (admin only)
Route::get('/role/management', [RoleController::class, 'index'])->name('role_management')->middleware(['auth', 'can:create,App\\Models\\Role']);
Route::put('/users/{user}/roles', [RoleController::class, 'updateRoles'])->name('update_roles')->middleware(['auth', 'can:create,App\\Models\Role']);
Route::delete('/users/{user}', [RoleController::class, 'deleteUser'])->name('delete_user')->middleware(['auth', 'can:delete,user']);

// Reference routes
Route::get('/references/configure', [ReferenceController::class, 'index'])->name('references_configure')->middleware(['auth', 'can:update,App\\Models\\Reference']);
Route::get('/create/references', [ReferenceController::class, 'create'])->name('references_create')->middleware(['auth', 'can:update,App\\Models\\Reference']);
Route::post('/references/store', [ReferenceController::class, 'store'])->name('references_store')->middleware(['auth', 'can:update,App\\Models\\Reference']);
Route::delete('/references/{bible}', [ReferenceController::class, 'destroy'])->name('references_destroy')->middleware(['auth', 'can:update,App\\Models\\Reference']);
Route::get('/api/verses/{verse}/references', [ReferenceController::class, 'getVerseReferences'])->name('verse_references');
Route::get('/verses/{verse}/study', [ReferenceController::class, 'studyVerse'])->name('verse_study');

// Verse highlight routes
Route::post('/api/verse-highlights', [VerseHighlightController::class, 'store'])->name('verse_highlights_store')->middleware('auth');
Route::delete('/api/verse-highlights/{verse}', [VerseHighlightController::class, 'destroy'])->name('verse_highlights_destroy')->middleware('auth');
Route::get('/api/verse-highlights', [VerseHighlightController::class, 'index'])->name('verse_highlights_index')->middleware('auth');
Route::get('/api/verse-highlights/chapter', [VerseHighlightController::class, 'getForChapter'])->name('verse_highlights_chapter')->middleware('auth');
Route::get('/highlighted-verses', [VerseHighlightController::class, 'highlightedVersesPage'])->name('highlighted_verses_page')->middleware('auth');
Route::put('/api/verse/{verse}', [VerseController::class, 'update'])->name('verse_update')->middleware('auth');

// Note routes
Route::get('/notes', [NoteController::class, 'index'])->name('notes')->middleware('auth');
Route::get('/api/notes', [NoteController::class, 'index'])->name('notes_index')->middleware('auth');
Route::post('/api/notes', [NoteController::class, 'store'])->name('notes_store')->middleware('auth');
Route::get('/api/notes/{note}', [NoteController::class, 'show'])->name('notes_show')->middleware('auth');
Route::put('/api/notes/{note}', [NoteController::class, 'update'])->name('notes_update')->middleware('auth');
Route::delete('/api/notes/{note}', [NoteController::class, 'destroy'])->name('notes_destroy')->middleware('auth');
Route::get('/api/notes/verse/{verse}', [NoteController::class, 'getNotesForVerse'])->name('notes_for_verse')->middleware('auth');

// Reading Progress routes
Route::get('/reading-plan', [ReadingProgressController::class, 'readingPlan'])->name('reading_plan')->middleware('auth');
Route::post('/api/reading-progress/toggle', [ReadingProgressController::class, 'toggleChapter'])->name('reading_progress_toggle')->middleware('auth');
Route::get('/api/reading-progress/bible', [ReadingProgressController::class, 'getBibleProgress'])->name('reading_progress_bible')->middleware('auth');
Route::get('/api/reading-progress/statistics', [ReadingProgressController::class, 'getStatistics'])->name('reading_progress_statistics')->middleware('auth');

// Lesson
Route::get('/create/lesson', [LessonController::class, 'create'])->name('create_lesson')->middleware('auth');
Route::get('/edit/lesson/{lesson}', [LessonController::class, 'edit'])->name('edit_lesson')->middleware('auth');
Route::get('/manage/lessons', [LessonController::class, 'manage'])->name('manage_lessons')->middleware('auth');
Route::post('/create/lesson', [LessonController::class, 'store'])->name('store_lesson')->middleware('auth');
Route::put('/update/lesson/{lesson}', [LessonController::class, 'update'])->name('update_lesson')->middleware('auth');
Route::delete('/lessons/{lesson}', [LessonController::class, 'destroy'])->name('delete_lesson')->middleware('auth');
Route::get('/lessons', [LessonController::class, 'index'])->name('lessons')->middleware('auth');
Route::get('/lessons/show/{lesson}', [LessonController::class, 'show'])->name('show_lesson')->middleware('auth');
Route::post('/api/lesson/progress', [LessonController::class, 'toggleProgress'])->name('lesson_progress_toggle')->middleware('auth');

// Verse Link Canvas routes
Route::get('/verse-link', [VerseLinkController::class, 'index'])->name('verse_link')->middleware('auth');
Route::post('/api/verse-link/canvas', [VerseLinkController::class, 'storeCanvas'])->name('verse_link_store_canvas')->middleware('auth');
Route::get('/api/verse-link/canvas/{canvas}', [VerseLinkController::class, 'showCanvas'])->name('verse_link_show_canvas')->middleware('auth');
Route::put('/api/verse-link/canvas/{canvas}', [VerseLinkController::class, 'updateCanvas'])->name('verse_link_update_canvas')->middleware('auth');
Route::delete('/api/verse-link/canvas/{canvas}', [VerseLinkController::class, 'destroyCanvas'])->name('verse_link_destroy_canvas')->middleware('auth');
Route::post('/api/verse-link/node', [VerseLinkController::class, 'storeNode'])->name('verse_link_store_node')->middleware('auth');
Route::put('/api/verse-link/node/{node}', [VerseLinkController::class, 'updateNode'])->name('verse_link_update_node')->middleware('auth');
Route::delete('/api/verse-link/node/{node}', [VerseLinkController::class, 'destroyNode'])->name('verse_link_destroy_node')->middleware('auth');
Route::get('/api/verse-link/node/{node}/references', [VerseLinkController::class, 'getNodeReferences'])->name('verse_link_node_references')->middleware('auth');
Route::post('/api/verse-link/connection', [VerseLinkController::class, 'storeConnection'])->name('verse_link_store_connection')->middleware('auth');
Route::delete('/api/verse-link/connection/{connection}', [VerseLinkController::class, 'destroyConnection'])->name('verse_link_destroy_connection')->middleware('auth');
Route::get('/api/verse-link/search', [VerseLinkController::class, 'searchVerses'])->name('verse_link_search')->middleware('auth');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
