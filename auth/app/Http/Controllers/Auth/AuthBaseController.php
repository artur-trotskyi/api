<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

abstract class AuthBaseController extends Controller
{
    abstract public function register(AuthRegisterRequest $request): JsonResponse;

    abstract public function login(AuthLoginRequest $request);

    abstract public function me();

    abstract public function logout();

    abstract public function refresh(Request $request);
}
