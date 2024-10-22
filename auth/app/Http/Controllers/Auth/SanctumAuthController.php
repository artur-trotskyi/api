<?php

// https://medium.com/@dychkosergey/access-and-refresh-tokens-using-laravel-sanctum-037392e50509
// https://medium.com/@marcboko.uriel/manage-refresh-token-and-acces-token-with-laravel-sanctum-85defbce46ed

namespace App\Http\Controllers\Auth;

use App\Enums\ExceptionMessagesEnum;
use App\Enums\ResourceMessagesEnum;
use App\Enums\TokenAbilityEnum;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use App\Services\AuthService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SanctumAuthController extends AuthBaseController
{
    private AuthService $authService;

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', only: ['logout', 'refresh', 'me']),
            new Middleware('ability:' . TokenAbilityEnum::ISSUE_ACCESS_TOKEN->message(), only: ['refresh']),
            new Middleware('ability:' . TokenAbilityEnum::ACCESS_API->message(), only: ['me']),
        ];
    }

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
        $cookie = $this->authService
            ->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $userData = [
            'user' => $user,
            'access_token' => $tokens['access']['accessToken'],
            'token_type' => 'Bearer',
            'expires_in' => $tokens['access']['accessTokenExpireTime'],
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::RegisterSuccessful->message()))
            ->response()
            ->withCookie($cookie)
            ->setStatusCode(201);
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
        $cookie = $this->authService->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $userData = [
            'user' => $user,
            'access_token' => $tokens['access']['accessToken'],
            'token_type' => 'Bearer',
            'expires_in' => $tokens['access']['accessTokenExpireTime'],
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::LoginSuccessful->message()))
            ->response()
            ->withCookie($cookie);
    }

    /**
     * Get the authenticated User.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function me(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
        }

        $userData = [
            'user' => $user,
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::DataRetrievedSuccessfully->message()))
            ->response();
    }

    /**
     *  Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws Exception
     */
    public function logout(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
        }

        try {
            $user->tokens()->delete();
        } catch (Exception $e) {
            throw new Exception(ExceptionMessagesEnum::UnableToRevokeTokens->message());
        }

        return (new AuthResource([], ResourceMessagesEnum::YouAreLoggedOut->message()))
            ->response();
    }

    /**
     * Refresh access token.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws Exception
     */
    public function refresh(): JsonResponse
    {
        $user = Auth::guard('sanctum')->user();
        if (!$user) {
            throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
        }

        try {
            $user->tokens()->delete();
        } catch (Exception $e) {
            throw new Exception(ExceptionMessagesEnum::UnableToRevokeTokens->message());
        }

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService
            ->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $tokenData = [
            'access_token' => $tokens['access']['accessToken'],
            'token_type' => 'Bearer',
            'expires_in' => $tokens['access']['accessTokenExpireTime'],
        ];

        return (new AuthResource($tokenData, ResourceMessagesEnum::LoginSuccessful->message()))
            ->response()
            ->withCookie($cookie);
    }
}
