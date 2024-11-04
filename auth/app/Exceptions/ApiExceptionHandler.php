<?php

namespace App\Exceptions;

use App\Enums\ExceptionMessagesEnum;
use App\Http\Resources\ErrorResource;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
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
     */
    public function __construct(Throwable $exception)
    {
        $this->handle($exception);
    }

    /**
     * Handle exceptions and set the appropriate message and code
     */
    public function handle(Throwable $exception): void
    {
        switch (true) {
            case $exception instanceof InvalidArgumentException:
                $this->message = ExceptionMessagesEnum::ValidationError->message();
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof QueryException:
                $this->message = ExceptionMessagesEnum::InternalServerError->message();
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = ExceptionMessagesEnum::FailedToRetrieveData->message();
                break;
            case $exception instanceof AccessDeniedHttpException || $exception instanceof AuthorizationException:
                $this->message = ExceptionMessagesEnum::UnauthorizedAction->message();
                $this->code = Response::HTTP_FORBIDDEN;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof AuthenticationException:
                $this->message = $exception->getMessage();
                $this->code = Response::HTTP_UNAUTHORIZED;
                $this->errors[] = ExceptionMessagesEnum::AuthenticationRequired->message();
                break;
            case $exception instanceof ValidationException:
                $this->message = ExceptionMessagesEnum::TheGivenDataWasInvalid->message();
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $exception->getMessage();
                break;
            case $exception instanceof NotFoundHttpException:
                $this->message = ExceptionMessagesEnum::NotFound->message();
                $this->code = Response::HTTP_NOT_FOUND;
                $this->errors[] = ExceptionMessagesEnum::ResourceNotFound->message();
                break;
            case $exception instanceof ThrottleRequestsException:
                $this->message = ExceptionMessagesEnum::TooManyRequests->message();
                $this->code = Response::HTTP_TOO_MANY_REQUESTS;
                $this->errors[] = $exception->getMessage();
                break;
            default:
                $this->message = ExceptionMessagesEnum::InternalServerError->message();
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = $exception->getMessage() ?? ExceptionMessagesEnum::AnUnknownErrorOccurred->message();
                break;
        }
    }

    /**
     * Render the response for the API.
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
