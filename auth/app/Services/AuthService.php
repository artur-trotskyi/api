<?php

namespace App\Services;

use App\Enums\AuthDriverEnum;
use App\Enums\ExceptionMessagesEnum;
use App\Enums\TokenAbilityEnum;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService extends BaseService
{
    public function __construct() {}

    /**
     * Resolves and returns an instance of the appropriate authentication controller.
     *
     * @throws InvalidArgumentException If the auth driver is not supported.
     */
    public function resolveAuthController(): string
    {
        $authDriver = config('auth.auth_driver');
        if (! AuthDriverEnum::isValid($authDriver)) {
            throw new InvalidArgumentException(ExceptionMessagesEnum::unsupportedDriverMessage($authDriver));
        }

        $driverEnum = AuthDriverEnum::from($authDriver);

        return $driverEnum->controllerClass();
    }

    /**
     * Generate access and refresh tokens for the authenticated user.
     *
     * @param  User|Authenticatable  $user  The authenticated user instance.
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
     */
    public function generateRefreshTokenCookie(string $refreshToken, int $rtExpireTime): Application|CookieJar|Cookie
    {
        return cookie('refreshToken', $refreshToken, $rtExpireTime, secure: config('app.is_production'));
    }
}
