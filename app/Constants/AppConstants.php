<?php

namespace App\Constants;

/**
 * Interface App\Constants
 */
interface AppConstants
{
    public const array EXCEPTION_MESSAGES = [
        'authorization_for_data' => 'The current user does not have ownership rights to the requested resource.',
        'unauthorized_action' => 'Unauthorized action.',
        'authentication_required' => 'Authentication is required to access this resource.',
        'invalid_uuid_with_field' => 'The given ID is not a valid UUID.',
        'invalid_uuid_for_route_key' => 'The given ID is not a valid UUID for route key.',
        'not_found' => 'Not Found.',
        'data_not_found' => 'Data not found.',
        'internal_server_error' => 'Internal server error.',
        'an_unknown_error_occurred' => 'An unknown error occurred.',
        'the_given_data_was_invalid' => 'The given data was invalid.',
        'failed_to_retrieve_data' => 'Failed to retrieve data.',
        'the_provided_credentials_are_incorrect' => 'The provided credentials are incorrect.',
        'resource_not_found' => 'The requested resource could not be found.',
        'validation_error' => 'Validation error.',
    ];

    public const array RESOURCE_MESSAGES = [
        'default_successfully' => 'Request processed successfully.',
        'default_failed' => 'Request failed.',
        'data_retrieved_successfully' => 'Data retrieved successfully.',
        'data_created_successfully' => 'Data created successfully.',
        'data_updated_successfully' => 'Data updated successfully.',
        'data_deleted_successfully' => 'Data deleted successfully.',
        'register_successful' => 'Register successful.',
        'login_successful' => 'Login successful.',
        'you_are_logged_out' => 'You are logged out.',
    ];

    public const int ALL = -1;
    public const array TAGS = ['php', 'ruby', 'java', 'javascript', 'bash'];
    public const array SORTABLE_FIELDS = ['title', 'content'];
    public const array SORT_ORDER_OPTIONS = ['asc', 'desc'];
}
