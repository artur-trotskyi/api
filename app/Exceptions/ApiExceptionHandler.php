<?php

namespace App\Exceptions;

use App\Constants\AppConstants;
use App\Http\Resources\ErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler
{
    protected array $errors = [];
    protected string $message = '';
    protected int $code = Response::HTTP_NOT_FOUND;

    /**
     * Constructor for exception handling
     *
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->handle($exception);
    }

    /**
     * Handle exceptions and set the appropriate message and code
     *
     * @param Throwable $exception
     */
    public function handle(Throwable $exception): void
    {
        switch (true) {
            case $exception instanceof InvalidArgumentException:
                $this->message = AppConstants::EXCEPTION_MESSAGES['validation_error'];
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof QueryException:
                $this->message = AppConstants::EXCEPTION_MESSAGES['internal_server_error'];
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = AppConstants::EXCEPTION_MESSAGES['failed_to_retrieve_data'];
                break;
            case $exception instanceof AccessDeniedHttpException || $exception instanceof AuthorizationException:
                $this->message = AppConstants::EXCEPTION_MESSAGES['unauthorized_action'];
                $this->code = Response::HTTP_FORBIDDEN;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof AuthenticationException:
                $this->message = $exception->getMessage();
                $this->code = Response::HTTP_UNAUTHORIZED;
                $this->errors[] = AppConstants::EXCEPTION_MESSAGES['authentication_required'];
                break;
            case $exception instanceof ValidationException:
                $this->message = AppConstants::EXCEPTION_MESSAGES['the_given_data_was_invalid'];
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof NotFoundHttpException:
                $this->message = AppConstants::EXCEPTION_MESSAGES['not_found'];
                $this->code = Response::HTTP_NOT_FOUND;
                $this->errors[] = AppConstants::EXCEPTION_MESSAGES['resource_not_found'];
                break;
            default:
                $this->message = AppConstants::EXCEPTION_MESSAGES['internal_server_error'];
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = AppConstants::EXCEPTION_MESSAGES['an_unknown_error_occurred'];
                break;
        }
    }

    /**
     * Render the response for the API.
     *
     * @param Request $request
     * @return ErrorResource
     */
    public function render(Request $request): ErrorResource
    {
        $errorData = ['errors' => $this->errors];
        return new ErrorResource(
            $errorData,
            $this->message,
            $this->code
        );
    }
}
