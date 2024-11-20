<?php

// https://medium.com/@dychkosergey/access-and-refresh-tokens-using-laravel-sanctum-037392e50509
// https://github.com/dychkos/laravel-access-refresh-tokens
// https://medium.com/@marcboko.uriel/manage-refresh-token-and-acces-token-with-laravel-sanctum-85defbce46ed

namespace App\Http\Controllers\Auth;

use App\Dto\UserTokenDto;
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
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class SanctumAuthController extends AuthBaseController
{
    private AuthService $authService;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        AuthService $authService
    ) {
        $this->authService = $authService;
    }

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:sanctum', only: ['logout', 'refresh', 'me']),
            new Middleware('ability:'.TokenAbilityEnum::ISSUE_ACCESS_TOKEN->message(), only: ['refresh']),
            new Middleware('ability:'.TokenAbilityEnum::ACCESS_API->message(), only: ['me']),
        ];
    }

    /**
     * Register a new user.
     */
    public function register(AuthRegisterRequest $request): AuthResource
    {
        $registerRequestData = $request->validated();
        $user = User::create($registerRequestData);

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService
            ->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $userTokenDto = UserTokenDto::make(
            accessToken: $tokens['access']['accessToken'],
            expiresIn: $tokens['access']['accessTokenExpireTime'],
            user: $user,
        )->toArray();

        return AuthResource::make($userTokenDto, ResourceMessagesEnum::RegisterSuccessful->message())
            ->setCookie($cookie)
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Log a user and get a token via given credentials.
     *
     * @throws AuthenticationException
     * @throws ValidationException
     */
    public function login(AuthLoginRequest $request): AuthResource
    {
        $loginRequestData = $request->validated();

        $user = User::where('email', $loginRequestData['email'])->first();
        if (! $user || ! Hash::check($loginRequestData['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [ExceptionMessagesEnum::TheProvidedCredentialsAreIncorrect->message()],
            ]);
        }

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $userTokenDto = UserTokenDto::make(
            accessToken: $tokens['access']['accessToken'],
            expiresIn: $tokens['access']['accessTokenExpireTime'],
            user: $user,
        )->toArray();

        return AuthResource::make($userTokenDto, ResourceMessagesEnum::LoginSuccessful->message())
            ->setCookie($cookie);
    }

    /**
     * Get the authenticated user.
     *
     * @throws AuthenticationException
     */
    public function me(): AuthResource
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
        }

        $userData = [
            'user' => $user,
        ];

        return AuthResource::make($userData, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function logout(): AuthResource
    {
        $refreshToken = request()->cookie('refreshToken') ?? null;
        if (! $refreshToken) {
            $cookie = cookie()->forget('refreshToken');

            return AuthResource::make([], ResourceMessagesEnum::AlreadyLoggedOut->message())
                ->setCookie($cookie);
        }

        $personalAccessToken = PersonalAccessToken::findToken($refreshToken);
        if ($personalAccessToken) {
            try {
                $personalAccessToken->tokenable->tokens()->delete();
            } catch (Exception $e) {
                throw new Exception(ExceptionMessagesEnum::UnableToRevokeTokens->message());
            }
        }

        $cookie = cookie()->forget('refreshToken');

        return AuthResource::make([], ResourceMessagesEnum::YouAreLoggedOut->message())
            ->setCookie($cookie);
    }

    /**
     * Refresh access token.
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function refresh(): AuthResource
    {
        $user = Auth::guard('sanctum')->user();
        if (! $user) {
            throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
        }

        try {
            // Revoke all tokens
            $user->tokens()->delete();
            // Revoke the current token
            // $user->currentAccessToken()->delete();
        } catch (Exception $e) {
            throw new Exception(ExceptionMessagesEnum::UnableToRevokeTokens->message());
        }

        $tokens = $this->authService->generateTokens($user);
        $cookie = $this->authService
            ->generateRefreshTokenCookie($tokens['refresh']['refreshToken'], $tokens['refresh']['refreshTokenExpireTime']);

        $userTokenDto = UserTokenDto::make(
            accessToken: $tokens['access']['accessToken'],
            expiresIn: $tokens['access']['accessTokenExpireTime'],
        )->toArray();

        return AuthResource::make($userTokenDto, ResourceMessagesEnum::LoginSuccessful->message())
            ->setCookie($cookie);
    }
}
