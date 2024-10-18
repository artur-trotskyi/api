<?php

use App\Http\Controllers\JWTAuthController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', [JWTAuthController::class, 'login'])->name('login');
    Route::post('logout', [JWTAuthController::class, 'logout'])->name('logout');
    Route::post('refresh', [JWTAuthController::class, 'refresh'])->name('refresh');
    Route::post('me', [JWTAuthController::class, 'me'])->name('me');
});
