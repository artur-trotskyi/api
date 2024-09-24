<?php

namespace App\Services;

use App\Enums\TokenAbility;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Cookie\CookieJar;
use Illuminate\Foundation\Application;
use Symfony\Component\HttpFoundation\Cookie;

class AuthService extends BaseService
{
    public function __construct()
    {
        //
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
        $atExpireTime = now()->addMinutes(config('sanctum.expiration'));
        $rtExpireTime = now()->addMinutes(config('sanctum.rt_expiration'));

        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API], $atExpireTime);
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN], $rtExpireTime);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
        ];
    }

    /**
     * Generates a secure refresh token cookie.
     * 
     * @param string $refreshToken
     * @return Application|CookieJar|Cookie
     */
    public function generateRefreshTokenCookie(string $refreshToken): Application|CookieJar|Cookie
    {
        $rtExpireTime = config('sanctum.rt_expiration');

        return cookie('refreshToken', $refreshToken, $rtExpireTime, secure: config('app.is_production'));
    }
}
