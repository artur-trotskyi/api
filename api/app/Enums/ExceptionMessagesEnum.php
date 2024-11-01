<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ExceptionMessagesEnum: string
{
    use EnumTrait;

    case AuthorizationForData = 'The current user does not have ownership rights to the requested resource.';
    case UnauthorizedAction = 'Unauthorized action.';
    case AuthenticationRequired = 'Authentication is required to access this resource.';
    case InvalidUuidWithField = 'The given ID is not a valid UUID.';
    case InvalidUuidForRouteKey = 'The given ID is not a valid UUID for route key.';
    case NotFound = 'Not Found.';
    case DataNotFound = 'Data not found.';
    case InternalServerError = 'Internal server error.';
    case AnUnknownErrorOccurred = 'An unknown error occurred.';
    case TheGivenDataWasInvalid = 'The given data was invalid.';
    case FailedToRetrieveData = 'Failed to retrieve data.';
    case TheProvidedCredentialsAreIncorrect = 'The provided credentials are incorrect.';
    case ResourceNotFound = 'The requested resource could not be found.';
    case ValidationError = 'Validation error.';
    case TooManyRequests = 'You have exceeded the allowed number of requests.';
}
