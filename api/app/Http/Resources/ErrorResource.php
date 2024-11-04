<?php

namespace App\Http\Resources;

use App\Enums\ResourceMessagesEnum;
use Symfony\Component\HttpFoundation\Response;

class ErrorResource extends BaseResource
{
    protected string $message;

    protected int $statusCode;

    protected bool $success;

    public function __construct(
        $resource,
        string $message = ResourceMessagesEnum::DefaultFailed->value,
        int $statusCode = Response::HTTP_BAD_REQUEST,
        bool $success = false
    ) {
        parent::__construct($resource, $message, $statusCode, $success);
    }
}
