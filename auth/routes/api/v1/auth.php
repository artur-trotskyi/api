<?php

use App\Services\AuthService;
use Illuminate\Support\Facades\Route;

$authService = app(AuthService::class);
$authController = $authService->getAuthController();

Route::prefix('auth')->as('auth.')->group(function () use ($authController) {
    Route::post('register', [$authController, 'register'])->name('register');
    Route::post('login', [$authController, 'login'])->name('login');
    Route::post('logout', [$authController, 'logout'])->name('logout');
    Route::post('refresh-token', [$authController, 'refresh'])->name('refresh');
    Route::post('me', [$authController, 'me'])->name('me');
});
