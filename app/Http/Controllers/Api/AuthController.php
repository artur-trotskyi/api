<?php

// https://medium.com/@dychkosergey/access-and-refresh-tokens-using-laravel-sanctum-037392e50509

namespace App\Http\Controllers\Api;

use App\Constants\AppConstants;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * @param AuthRegisterRequest $request
     * @return AuthResource
     */
    public function register(AuthRegisterRequest $request): AuthResource
    {
        $registerRequestData = $request->validated();
        $user = User::create($registerRequestData);

        $token = $user->createToken($registerRequestData['name'])->plainTextToken;
        $userData = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return new AuthResource($userData, AppConstants::RESOURCE_MESSAGES['register_successful']);
    }

    /**
     * @param AuthLoginRequest $request
     * @return AuthResource
     * @throws AuthenticationException
     */
    public function login(AuthLoginRequest $request): AuthResource
    {
        $loginRequestData = $request->validated();

        $user = User::where('email', $loginRequestData['email'])->first();
        if (!$user || !Hash::check($loginRequestData['password'], $user->password)) {
            throw new AuthenticationException(AppConstants::EXCEPTION_MESSAGES['the_provided_credentials_are_incorrect']);
        }

        $token = $user->createToken($user->name)->plainTextToken;
        $userData = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return new AuthResource($userData, AppConstants::RESOURCE_MESSAGES['login_successful']);
    }

    /**
     * @param Request $request
     * @return AuthResource
     */
    public function logout(Request $request): AuthResource
    {
        $request->user()->tokens()->delete();

        return new AuthResource([], AppConstants::RESOURCE_MESSAGES['you_are_logged_out']);
    }
}
