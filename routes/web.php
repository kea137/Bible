<?php

use App\Http\Controllers\BibleController;
use App\Http\Controllers\DashboardController;
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

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
