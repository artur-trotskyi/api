<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');*/

Route::middleware('throttle:api')->prefix('v1')->group(base_path('routes/api/api_v1.php'));
