<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Symfony\Component\HttpFoundation\Response;

class BaseResource extends JsonResource
{
    protected string $message;
    protected int $statusCode;
    protected bool $success;

    public function __construct
    (
        mixed  $resource,
        string $message = 'Request processed successfully',
        int    $statusCode = Response::HTTP_OK,
        bool   $success = true
    )
    {
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
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        if (!empty($this->resource->created_at) && $this->resource->created_at instanceof Carbon) {
            $data['created_at'] = $this->resource->created_at->toDateTimeString();
        }
        if (!empty($this->resource->updated_at) && $this->resource->updated_at instanceof Carbon) {
            $data['updated_at'] = $this->resource->updated_at->toDateTimeString();
        }

        return $data;
    }

    /**
     * @param Request $request
     * @return array
     */
    private function withResponseData(Request $request): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->toArray($request),
        ];
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function toResponse($request): JsonResponse
    {
        return response()->json(
            $this->withResponseData($request),
            $this->statusCode
        );
    }
}
