<?php

use App\Http\Controllers\Settings\DataExportController;
use App\Http\Controllers\Settings\LanguageController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\TwoFactorAuthenticationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance.edit');

    Route::get('settings/offline', function () {
        return Inertia::render('settings/Offline');
    })->name('offline.edit');

    Route::get('settings/privacy', function () {
        return Inertia::render('settings/Privacy');
    })->name('privacy.edit');

    Route::patch('settings/language', [LanguageController::class, 'update'])->name('language.update');

    Route::get('settings/two-factor', [TwoFactorAuthenticationController::class, 'show'])
        ->name('two-factor.show');

    // Data export route
    Route::get('settings/export-data', [DataExportController::class, 'export'])->name('data.export');
});
