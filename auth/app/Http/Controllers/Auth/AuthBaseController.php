<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;

abstract class AuthBaseController extends Controller
{
    /**
     * Register a new user.
     */
    abstract public function register(AuthRegisterRequest $request): AuthResource;

    /**
     * Log a user and get a token via given credentials.
     */
    abstract public function login(AuthLoginRequest $request): AuthResource;

    /**
     * Get the authenticated user.
     */
    abstract public function me(): AuthResource;

    /**
     * Log the user out (Invalidate the token).
     */
    abstract public function logout(): AuthResource;

    /**
     * Refresh access token.
     */
    abstract public function refresh(): AuthResource;
}
