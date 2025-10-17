<?php

use App\Http\Controllers\BibleController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReferenceController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/license', function () {
    return Inertia::render('License');
})->name('license');

Route::get('/bibles', [BibleController::class, 'index'])->name('bibles');
Route::get('/bibles/parallel', [BibleController::class, 'parallel'])->name('bibles_parallel');
Route::get('/bibles/{bible}', [BibleController::class, 'show'])->name('bible_show');
Route::get('/bibles/upload/bible', [BibleController::class, 'create'])->name('bible_create');
Route::post('/bibles/create/bible', [BibleController::class, 'store'])->name('bible_store');
Route::get('/api/bibles/books/chapters/{chapter}', [BibleController::class, 'showChapter'])->name('bible_show_chapter');

// Role Management routes (admin only)
Route::get('/role/management', [RoleController::class, 'index'])->name('role_management')->middleware('auth');
Route::put('/users/{user}/roles', [RoleController::class, 'updateRoles'])->name('update_roles')->middleware('auth');

// Reference routes
Route::get('/create/{bible}/references', [ReferenceController::class, 'create'])->name('references')->middleware('auth');
Route::post('/references/store', [ReferenceController::class, 'store'])->name('references_store')->middleware('auth');
Route::get('/api/verses/{verse}/references', [ReferenceController::class, 'getVerseReferences'])->name('verse_references');

// Verse highlight routes
Route::post('/api/verse-highlights', [\App\Http\Controllers\VerseHighlightController::class, 'store'])->name('verse_highlights_store')->middleware('auth');
Route::delete('/api/verse-highlights/{verse}', [\App\Http\Controllers\VerseHighlightController::class, 'destroy'])->name('verse_highlights_destroy')->middleware('auth');
Route::get('/api/verse-highlights', [\App\Http\Controllers\VerseHighlightController::class, 'index'])->name('verse_highlights_index')->middleware('auth');
Route::get('/api/verse-highlights/chapter', [\App\Http\Controllers\VerseHighlightController::class, 'getForChapter'])->name('verse_highlights_chapter')->middleware('auth');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
