<?php

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
*/

Route::middleware('throttle:60,1')->group(function () {
    Route::get('/verses', [PublicBibleController::class, 'verses']);
});
