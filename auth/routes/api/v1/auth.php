<?php

use App\Services\AuthService;
use Illuminate\Support\Facades\Route;

// Resolve the appropriate authentication controller instance
/** @var AuthService $authService */
$authService = app(AuthService::class);
$authController = $authService->resolveAuthController();

Route::prefix('auth')->as('auth.')->group(function () use ($authController): void {
    Route::post('register', [$authController, 'register'])->name('register');
    Route::post('login', [$authController, 'login'])->name('login');
    Route::post('logout', [$authController, 'logout'])->name('logout');
    Route::post('refresh-token', [$authController, 'refresh'])->name('refresh');
    Route::post('me', [$authController, 'me'])->name('me');
});
