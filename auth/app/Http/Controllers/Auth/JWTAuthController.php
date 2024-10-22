<?php
// https://medium.com/@a3rxander/how-to-implement-jwt-authentication-in-laravel-11-26e6d7be5a41

namespace App\Http\Controllers\Auth;

use App\Enums\ExceptionMessagesEnum;
use App\Enums\ResourceMessagesEnum;
use App\Http\Requests\Auth\AuthLoginRequest;
use App\Http\Requests\Auth\AuthRegisterRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Models\User;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends AuthBaseController implements HasMiddleware
{
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
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Register a new user.
     *
     * @param AuthRegisterRequest $request
     * @return JsonResponse
     */
    public function register(AuthRegisterRequest $request): JsonResponse
    {
        $registerRequestData = $request->validated();
        $user = User::create($registerRequestData);
        $token = JWTAuth::fromUser($user);

        $userData = [
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::RegisterSuccessful->message()))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Log a user and get a token via given credentials.
     *
     * @param AuthLoginRequest $request
     * @return JsonResponse
     * @throws AuthenticationException
     * @throws Exception
     */
    public function login(AuthLoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');
        try {
            $token = JWTAuth::attempt($credentials);
            if (!$token) {
                throw new AuthenticationException(ExceptionMessagesEnum::TheProvidedCredentialsAreIncorrect->message());
            }

            // Get the authenticated user.
            $user = JWTAuth::user();
            // (optional) Attach the role to the token.
            // $token = JWTAuth::claims(['role' => $user->role])->fromUser($user);

            $userData = [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];

            return (new AuthResource($userData, ResourceMessagesEnum::LoginSuccessful->message()))
                ->response();
        } catch (JWTException $e) {
            throw new Exception(ExceptionMessagesEnum::CouldNotCreateToken->message());
        }
    }

    /**
     * Get the authenticated user.
     *
     * @return JsonResponse
     * @throws AuthenticationException
     */
    public function me(): JsonResponse
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                throw new AuthenticationException(ExceptionMessagesEnum::AuthenticationRequired->message());
            }
        } catch (JWTException $e) {
            throw new AuthenticationException(ExceptionMessagesEnum::InvalidToken->message());
        }

        $userData = [
            'user' => $user,
        ];

        return (new AuthResource($userData, ResourceMessagesEnum::DataRetrievedSuccessfully->message()))
            ->response();
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return (new AuthResource([], ResourceMessagesEnum::YouAreLoggedOut->message()))
            ->response();
    }

    /**
     * Refresh access token.
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function refresh(): JsonResponse
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());
            $tokenData = [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ];

            return (new AuthResource($tokenData, ResourceMessagesEnum::LoginSuccessful->message()))
                ->response();

        } catch (TokenBlacklistedException $e) {
            throw new AuthenticationException(ExceptionMessagesEnum::TokenHasBeenBlacklisted->message());
        } catch (JWTException $e) {
            throw new Exception(ExceptionMessagesEnum::CouldNotRefreshToken->message());
        }
    }
}
