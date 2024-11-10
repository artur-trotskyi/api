<?php

namespace App\Http\Resources;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BaseResourceCollection extends ResourceCollection
{
    protected bool $success = true;

    protected string $message = '';

    /**
     * Set success and message properties.
     *
     * @return $this
     */
    public function withStatusMessage(bool $success, string $message): self
    {
        $this->success = $success;
        $this->message = $message;

        return $this;
    }

    /**
     * Customize the response for the resource.
     */
    public function withResponse(Request $request, JsonResponse $response): void
    {
        $response->setData([
            'success' => $this->success,
            'message' => $this->message,
            'data' => $response->getData(),
        ]);
    }
}
