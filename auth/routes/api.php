<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['throttle:api', 'api'])->prefix('v1')->group(base_path('routes/api/v1/auth.php'));
