<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ResourceMessagesEnum: string
{
    use EnumTrait;

    case DefaultSuccessfully = 'Request processed successfully.';
    case DefaultFailed = 'Request failed.';
    case DataRetrievedSuccessfully = 'Data retrieved successfully.';
    case DataCreatedSuccessfully = 'Data created successfully.';
    case DataUpdatedSuccessfully = 'Data updated successfully.';
    case DataDeletedSuccessfully = 'Data deleted successfully.';
    case RegisterSuccessful = 'Register successful.';
    case LoginSuccessful = 'Login successful.';
    case YouAreLoggedOut = 'You are successfully logged out.';
    case AlreadyLoggedOut = 'Already logged out.';
}
