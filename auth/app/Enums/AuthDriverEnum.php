<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum AuthDriverEnum: string
{
    use EnumTrait;

    case JWT = 'jwt';
    case SANCTUM = 'sanctum';

    // case OAUTH = 'oauth';

    /**
     * @param string $value
     * @return bool
     */
    public static function isValid(string $value): bool
    {
        return in_array($value, array_column(self::cases(), 'value'), true);
    }
}
