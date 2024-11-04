<?php

namespace App\Enums;

use App\Http\Controllers\Auth\JWTAuthController;
use App\Http\Controllers\Auth\SanctumAuthController;
use App\Traits\EnumTrait;

enum AuthDriverEnum: string
{
    use EnumTrait;

    case JWT = 'jwt';
    case SANCTUM = 'sanctum';

    public static function isValid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }

    // case OAUTH = 'oauth';

    /**
     * Get the corresponding authentication controller class for the enum case.
     */
    public function controllerClass(): ?string
    {
        return match ($this) {
            self::JWT => JWTAuthController::class,
            self::SANCTUM => SanctumAuthController::class,
            // self::OAUTH => \App\Http\Controllers\Auth\OAuthAuthController::class,
        };
    }
}
