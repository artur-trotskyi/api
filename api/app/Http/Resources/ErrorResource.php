<?php

namespace App\Http\Resources;

use App\Enums\ResourceMessagesEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorResource extends BaseResource
{
    /**
     * ErrorResource constructor.
     *
     * @param  mixed  $resource
     */
    public function __construct(
        $resource,
        protected string $message = ResourceMessagesEnum::DefaultFailed->value,
        protected int $statusCode = Response::HTTP_BAD_REQUEST,
        protected bool $success = false
    ) {
        parent::__construct($resource);
    }

    /**
     * Set additional data for the response.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setStatusCode($this->statusCode);

        $response->setData([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->resource,
        ]);
    }
}
