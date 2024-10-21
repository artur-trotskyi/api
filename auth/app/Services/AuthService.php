<?php

namespace App\Services;

use App\Enums\AuthDriverEnum;
use App\Enums\ExceptionMessagesEnum;
use App\Enums\TokenAbilityEnum;
use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Auth\SanctumAuthController;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService extends BaseService
{
    public function __construct()
    {
        //
    }

    /**
     * Retrieves the authentication controller class based on the selected driver.
     *
     * @return string The class name of the authentication controller.
     * @throws InvalidArgumentException If the specified driver is not supported.
     */
    public function getAuthController(): string
    {
        $authDriver = env('AUTH_DRIVER', 'jwt');

        if (!AuthDriverEnum::isValid($authDriver)) {
            throw new InvalidArgumentException(ExceptionMessagesEnum::unsupportedDriverMessage($authDriver));
        }

        $controllers = [
            AuthDriverEnum::JWT->message() => JWTAuthController::class,
            AuthDriverEnum::SANCTUM->message() => SanctumAuthController::class,
            // AuthDriverEnum::OAUTH->message() => OAuthAuthController::class,
        ];

        return $controllers[$authDriver];
    }

    /**
     * Generate access and refresh tokens for the authenticated user.
     *
     * @param User|Authenticatable $user The authenticated user instance.
     * @return array{
     *     accessToken: string,
     *     refreshToken: string,
     * }
     */
    public function generateTokens(User|Authenticatable $user): array
    {
        $expiration = config('sanctum.expiration') * 60;
        $atExpireTime = now()->addSeconds($expiration);
        $rtExpiration = config('sanctum.rt_expiration') * 60;
        $rtExpireTime = now()->addSeconds($rtExpiration);

        $accessToken = $user->createToken('access_token', [TokenAbilityEnum::ACCESS_API], $atExpireTime);
        $refreshToken = $user->createToken('refresh_token', [TokenAbilityEnum::ISSUE_ACCESS_TOKEN], $rtExpireTime);

        return [
            'access' => [
                'accessToken' => $accessToken->plainTextToken,
                'accessTokenExpireTime' => $expiration,
            ],
            'refresh' => [
                'refreshToken' => $refreshToken->plainTextToken,
                'refreshTokenExpireTime' => $rtExpiration,
            ],
        ];
    }

    /**
     * Generates a secure refresh token cookie.
     *
     * @param string $refreshToken
     * @param int $rtExpireTime
     * @return Application|CookieJar|Cookie
     */
    public function generateRefreshTokenCookie(string $refreshToken, int $rtExpireTime): Application|CookieJar|Cookie
    {
        return cookie('refreshToken', $refreshToken, $rtExpireTime, secure: config('app.is_production'));
    }
}
