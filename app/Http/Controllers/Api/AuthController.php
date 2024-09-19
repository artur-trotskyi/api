<?php

// https://medium.com/@dychkosergey/access-and-refresh-tokens-using-laravel-sanctum-037392e50509

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\ErrorResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

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

        return new AuthResource($userData, 'Register successful', Response::HTTP_OK);
    }

    /**
     * @param AuthLoginRequest $request
     * @return ErrorResource|AuthResource
     */
    public function login(AuthLoginRequest $request): ErrorResource|AuthResource
    {
        $loginRequestData = $request->validated();

        $user = User::where('email', $loginRequestData['email'])->first();
        if (!$user || !Hash::check($loginRequestData['password'], $user->password)) {
            return new ErrorResource(
                ['errors' => 'The provided credentials are incorrect'],
                'The provided credentials are incorrect',
                Response::HTTP_UNAUTHORIZED
            );
        }

        $token = $user->createToken($user->name)->plainTextToken;
        $userData = [
            'user' => $user,
            'token' => $token,
            'token_type' => 'Bearer'
        ];

        return new AuthResource($userData, 'Login successful', Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return AuthResource
     */
    public function logout(Request $request): AuthResource
    {
        $request->user()->tokens()->delete();

        return new AuthResource([], 'You are logged out', Response::HTTP_OK);
    }
}
