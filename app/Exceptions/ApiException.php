<?php

namespace App\Exceptions;

use App\Http\Resources\ErrorResource;
use Exception;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ApiException extends Exception
{
    protected array $errors = [];

    /**
     * @param Exception|null $exception
     * @param string|null $error
     * @param int|null $code
     */
    public function __construct(Exception $exception = null, string $error = null, int $code = null)
    {
        switch (true) {
            case $exception instanceof InvalidArgumentException:
                $message = 'Validation error';
                $code = $code ?: $exception->getCode() ?: 422;
                $this->errors[] = $error ?: $exception->getMessage();
                break;
            case $exception instanceof QueryException:
                $message = 'Internal server error';
                $code = $code ?: 500;
                $this->errors[] = $error ?: 'Error processing database query';
                break;
            default:
                $message = 'Internal server error';
                $code = $code ?: $exception->getCode() ?: 500;
                $this->errors[] = $error ?: 'Internal server error';
                break;
        }

        parent::__construct($message, $code, $exception);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function render(Request $request): mixed
    {
        $errorData = ['errors' => [$this->message]];
        $response = new ErrorResource(
            $errorData,
            $this->message,
            $this->code
        );

        throw new HttpResponseException($response->toResponse($request));
    }
}
