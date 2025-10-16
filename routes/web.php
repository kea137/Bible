<?php

use App\Http\Controllers\BibleController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/bibles', [BibleController::class, 'index'])->name('bibles');
Route::get('/bibles/{bible}', [BibleController::class, 'show'])->name('bible_show');
Route::get('/bibles/upload/bible', [BibleController::class, 'create'])->name('bible_create');
Route::post('/bibles/upload/bible', [BibleController::class, 'store'])->name('bible_store');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
