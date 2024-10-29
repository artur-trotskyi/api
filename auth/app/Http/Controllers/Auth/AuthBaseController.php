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
     *
     * @param AuthRegisterRequest $request
     * @return AuthResource
     */
    abstract public function register(AuthRegisterRequest $request): AuthResource;

    /**
     * Log a user and get a token via given credentials.
     *
     * @param AuthLoginRequest $request
     * @return AuthResource
     */
    abstract public function login(AuthLoginRequest $request): AuthResource;

    /**
     * Get the authenticated user.
     *
     * @return AuthResource
     */
    abstract public function me(): AuthResource;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return AuthResource
     */
    abstract public function logout(): AuthResource;

    /**
     * Refresh access token.
     *
     * @return AuthResource
     */
    abstract public function refresh(): AuthResource;
}
