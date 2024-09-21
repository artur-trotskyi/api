<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorResource;
use Exception;
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

class ApiExceptionHandler extends Exception
{
    protected array $errors = [];

    /**
     * Constructor for exception handling
     *
     * @param Exception|Throwable $exception
     * @param string|null $customError
     */
    public function __construct(Exception|Throwable $exception, string $customError = null)
    {
        $this->handle($exception, $customError);
    }

    /**
     * Handle exceptions and set the appropriate message and code
     *
     * @param Exception|Throwable $exception
     * @param string|null $customError
     */
    public function handle(Exception|Throwable $exception, string $customError = null): void
    {
        switch (true) {
            case $exception instanceof InvalidArgumentException:
                $this->message = 'Validation error';
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $customError ?: $exception->getMessage();
                break;
            case $exception instanceof QueryException:
                $this->message = 'Internal server error';
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = $customError ?: 'Error processing database query';
                break;
            case $exception instanceof AuthorizationException || $exception instanceof AccessDeniedHttpException:
                $this->message = 'Unauthorized action';
                $this->code = Response::HTTP_FORBIDDEN;
                $this->errors[] = $customError ?: $exception->getMessage();
                break;
            case $exception instanceof AuthenticationException:
                $this->message = 'Unauthenticated';
                $this->code = Response::HTTP_UNAUTHORIZED;
                $this->errors[] = $customError ?: $exception->getMessage();
                break;
            case $exception instanceof ValidationException:
                $this->message = 'Validation error';
                $this->code = Response::HTTP_UNPROCESSABLE_ENTITY;
                $this->errors[] = $customError ?: $exception->errors();
                break;
            case $exception instanceof NotFoundHttpException:
                $this->message = 'Not Found';
                $this->code = Response::HTTP_NOT_FOUND;
                $this->errors[] = $customError ?: 'Route Not found';
                break;
            default:
                $this->message = 'Internal server error';
                $this->code = Response::HTTP_INTERNAL_SERVER_ERROR;
                $this->errors[] = $customError ?: 'An unknown error occurred';
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
