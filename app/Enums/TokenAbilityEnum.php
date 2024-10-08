<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum TokenAbilityEnum: string
{
    use EnumTrait;

    case ISSUE_ACCESS_TOKEN = 'issue-access-token';
    case ACCESS_API = 'access-api';
}
