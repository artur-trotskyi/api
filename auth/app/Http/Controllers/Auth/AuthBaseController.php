<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use Illuminate\Http\JsonResponse;

abstract class AuthBaseController extends Controller
{
    /**
     * Register a new user.
     *
     * @param AuthRegisterRequest $request
     * @return JsonResponse
     */
    abstract public function register(AuthRegisterRequest $request): JsonResponse;

    /**
     * Log a user and get a token via given credentials.
     *
     * @param AuthLoginRequest $request
     * @return JsonResponse
     */
    abstract public function login(AuthLoginRequest $request): JsonResponse;

    /**
     * Get the authenticated user.
     *
     * @return JsonResponse
     */
    abstract public function me(): JsonResponse;

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    abstract public function logout(): JsonResponse;

    /**
     * Refresh access token.
     *
     * @return JsonResponse
     */
    abstract public function refresh(): JsonResponse;
}
