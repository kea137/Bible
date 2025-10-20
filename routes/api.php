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
