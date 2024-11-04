<?php

namespace App\Http\Resources;

use App\Enums\ResourceMessagesEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BaseResource extends JsonResource
{
    protected string $message;

    protected int $statusCode;

    protected bool $success;

    public function __construct(
        mixed $resource,
        string $message = ResourceMessagesEnum::DefaultSuccessfully->value,
        int $statusCode = Response::HTTP_OK,
        bool $success = true
    ) {
        parent::__construct($resource);
        $this->message = $message;
        $this->statusCode = $statusCode;
        $this->success = $success;
    }

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return is_array($this->resource) ? $this->resource : $this->resource->toArray();
    }

    /**
     * @param  Request  $request
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json(
            $this->withResponseData($request),
            $this->statusCode
        );
    }

    private function withResponseData(Request $request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->toArray($request),
        ];
    }
}
