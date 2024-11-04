<?php

// https://medium.com/@a3rxander/how-to-implement-jwt-authentication-in-laravel-11-26e6d7be5a41

namespace App\Http\Controllers\Auth;

use App\Dto\UserTokenDto;
use App\Enums\ExceptionMessagesEnum;
use App\Enums\ResourceMessagesEnum;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends AuthBaseController implements HasMiddleware
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {}

    /**
     * Get the middleware that should be assigned to the controller.
     */
    public static function middleware(): array
    {
        return [
            new Middleware('auth:api', except: ['login', 'refresh', 'register']),
        ];
    }

    /**
     * Register a new user.
     */
    public function register(AuthRegisterRequest $request): AuthResource
    {
        $registerRequestData = $request->validated();
        $user = User::create($registerRequestData);
        $token = JWTAuth::fromUser($user);

        $userTokenDto = UserTokenDto::make(
            accessToken: $token,
            expiresIn: JWTAuth::factory()->getTTL() * 60,
            user: $user,
        )->toArray();

        return AuthResource::make($userTokenDto, ResourceMessagesEnum::RegisterSuccessful->message())
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Log a user and get a token via given credentials.
     *
     * @throws AuthenticationException
     * @throws Exception
     */
    public function login(AuthLoginRequest $request): AuthResource
    {
        $credentials = $request->only('email', 'password');
        try {
            $token = JWTAuth::attempt($credentials);
            if (! $token) {
                throw new AuthenticationException(ExceptionMessagesEnum::TheProvidedCredentialsAreIncorrect->message());
            }

            // Get the authenticated user.
            $user = JWTAuth::user();
            // (optional) Attach the role to the token.
            // $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            $userTokenDto = UserTokenDto::make(
                accessToken: $token,
                expiresIn: JWTAuth::factory()->getTTL() * 60,
                user: $user,
            )->toArray();

            return AuthResource::make($userTokenDto, ResourceMessagesEnum::LoginSuccessful->message());
        } catch (JWTException $e) {
            throw new Exception(ExceptionMessagesEnum::CouldNotCreateToken->message());
        }
    }

    /**
     * Get the authenticated user.
     *
     * @throws AuthenticationException
     */
    public function me(): AuthResource
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (! $user) {
                throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
            }
        } catch (JWTException $e) {
            throw new AuthenticationException(ExceptionMessagesEnum::InvalidToken->message());
        }

        $userData = [
            'user' => $user,
        ];

        return AuthResource::make($userData, ResourceMessagesEnum::DataRetrievedSuccessfully->message());
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout(): AuthResource
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return AuthResource::make([], ResourceMessagesEnum::YouAreLoggedOut->message());
    }

    /**
     * Refresh access token.
     *
     * @throws Exception
     */
    public function refresh(): AuthResource
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            $userTokenDto = UserTokenDto::make(
                accessToken: $token,
                expiresIn: JWTAuth::factory()->getTTL() * 60,
            )->toArray();

            return AuthResource::make($userTokenDto, ResourceMessagesEnum::LoginSuccessful->message());

        } catch (TokenBlacklistedException $e) {
            throw new AuthenticationException(ExceptionMessagesEnum::TokenHasBeenBlacklisted->message());
        } catch (JWTException $e) {
            throw new Exception(ExceptionMessagesEnum::CouldNotRefreshToken->message());
        }
    }
}
