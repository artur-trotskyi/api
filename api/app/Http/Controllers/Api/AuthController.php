<?php

// https://medium.com/@dychkosergey/access-and-refresh-tokens-using-laravel-sanctum-037392e50509
// https://medium.com/@marcboko.uriel/manage-refresh-token-and-acces-token-with-laravel-sanctum-85defbce46ed

namespace App\Http\Controllers\Api;

use App\Enums\ExceptionMessagesEnum;
use App\Enums\ResourceMessagesEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    private AuthService $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct
    (
        AuthService $authService
    )
    {
        $this->authService = $authService;
    }

    /**
     * @param AuthRegisterRequest $request
     * @return JsonResponse
     */
    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $registerRequestData = $request->validated();
        $user = User::create($registerRequestData);

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService->generateRefreshTokenCookie($tokens['refreshToken']);

        $userData = [
            'user' => $user,
            'access_token' => $tokens['accessToken'],
            'token_type' => 'Bearer'
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::RegisterSuccessful->message()))
            ->response()
            ->withCookie($cookie);
    }

    /**
     * @param AuthLoginRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $loginRequestData = $request->validated();

        $user = User::where('email', $loginRequestData['email'])->first();
        if (!$user || !Hash::check($loginRequestData['password'], $user->password)) {
            throw new AuthenticationException(ExceptionMessagesEnum::TheProvidedCredentialsAreIncorrect->message());
        }

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService->generateRefreshTokenCookie($tokens['refreshToken']);

        $userData = [
            'user' => $user,
            'access_token' => $tokens['accessToken'],
            'token_type' => 'Bearer'
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::LoginSuccessful->message()))
            ->response()
            ->withCookie($cookie);
    }

    /**
     * @param Request $request
     * @return AuthResource
     */
    public function logout(Request $request): AuthResource
    {
        $request->user()->tokens()->delete();

        return new AuthResource([], ResourceMessagesEnum::YouAreLoggedOut->message());
    }

    /**
     * Refresh access token.
     *
     * Accept `{refreshToken: string}` from cookies.
     * @response array{data: array{accessToken: string}, status: bool}
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = Auth::user();
        $request->user()->tokens()->delete();

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService->generateRefreshTokenCookie($tokens['refreshToken']);

        $userData = [
            'access_token' => $tokens['accessToken'],
            'token_type' => 'Bearer'
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::LoginSuccessful->message()))
            ->response()
            ->withCookie($cookie);
    }
}
