<?php

use App\Http\Controllers\BibleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ReadingProgressController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\VerseHighlightController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/license', function () {
    return Inertia::render('License');
})->name('license');

Route::get('/documentation', function () {
    return Inertia::render('Documentation');
})->name('documentation');
// Share verse routes
Route::get('/share', [ShareController::class, 'index'])->name('share');

// Sitemap for SEO
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/bibles', [BibleController::class, 'index'])->name('bibles');
Route::get('/bibles/parallel', [BibleController::class, 'parallel'])->name('bibles_parallel');
Route::get('/bibles/configure', [BibleController::class, 'configure'])->name('bibles_configure')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::post('/bibles/bootup', [BibleController::class, 'bootup'])->name('bibles_bootup')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::get('/bibles/{bible}', [BibleController::class, 'show'])->name('bible_show');
Route::get('/bibles/upload/bible', [BibleController::class, 'create'])->name('bible_create')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::post('/bibles/create/bible', [BibleController::class, 'store'])->name('bible_store')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::get('/bibles/{bible}/edit', [BibleController::class, 'edit'])->name('bible_edit')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::put('/bibles/{bible}', [BibleController::class, 'update'])->name('bible_update')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::delete('/bibles/{bible}', [BibleController::class, 'destroy'])->name('bible_destroy')->middleware(['auth', 'can:update,App\\Models\\Bible']);
Route::get('/api/bibles', [BibleController::class, 'apiBiblesIndex'])->name('api_bibles');
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

// Note routes
Route::get('/notes', [NoteController::class, 'index'])->name('notes')->middleware('auth');
Route::get('/api/notes', [NoteController::class, 'index'])->name('notes_index')->middleware('auth');
Route::post('/api/notes', [NoteController::class, 'store'])->name('notes_store')->middleware('auth');
Route::get('/api/notes/{note}', [NoteController::class, 'show'])->name('notes_show')->middleware('auth');
Route::put('/api/notes/{note}', [NoteController::class, 'update'])->name('notes_update')->middleware('auth');
Route::delete('/api/notes/{note}', [NoteController::class, 'destroy'])->name('notes_destroy')->middleware('auth');

// Reading Progress routes
Route::get('/reading-plan', [ReadingProgressController::class, 'readingPlan'])->name('reading_plan')->middleware('auth');
Route::post('/api/reading-progress/toggle', [ReadingProgressController::class, 'toggleChapter'])->name('reading_progress_toggle')->middleware('auth');
Route::get('/api/reading-progress/bible', [ReadingProgressController::class, 'getBibleProgress'])->name('reading_progress_bible')->middleware('auth');
Route::get('/api/reading-progress/statistics', [ReadingProgressController::class, 'getStatistics'])->name('reading_progress_statistics')->middleware('auth');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
