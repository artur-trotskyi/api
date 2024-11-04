<?php

namespace Tests\Feature\Auth;

use App\Enums\TokenAbilityEnum;
use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class AuthTestHelper
{
    public static array $loginSuccessBody = [
        'accessToken',
        'user' => [
            'id',
            'email',
        ],
    ];

    public static function mockUser(): User
    {
        return User::factory()->create();
    }

    public static function clearUser(User $userModel): void
    {
        $userModel->tokens()->delete();
        $userModel->delete();
    }

    /**
     * @return array{
     *     accessToken: string,
     *     refreshToken: string,
     * }
     */
    public static function generateTokens(User $user): array
    {
        $atExpireTime = now()->addMinutes(config('sanctum.expiration'));
        $rtExpireTime = now()->addMinutes(config('sanctum.rt_expiration'));

        $accessToken = $user->createToken('access_token', [TokenAbilityEnum::ACCESS_API], $atExpireTime);
        $refreshToken = $user->createToken('refresh_token', [TokenAbilityEnum::ISSUE_ACCESS_TOKEN], $rtExpireTime);

        return [
            'accessToken' => $accessToken->plainTextToken,
            'refreshToken' => $refreshToken->plainTextToken,
        ];
    }

    public static function verifyAccessToken(string $accessToken): bool
    {
        $tokenInDb = PersonalAccessToken::findToken($accessToken);

        return $tokenInDb && $tokenInDb->expires_at->isFuture() && $tokenInDb->can(TokenAbilityEnum::ACCESS_API->message());
    }
}
