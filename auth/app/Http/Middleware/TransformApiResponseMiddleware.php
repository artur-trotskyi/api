<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TransformApiResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     * @return mixed|Response
     */
    public function handle(Request $request, Closure $next): mixed
    {
        $response = $next($request);

        // Transform keys of successful JSON responses to camelCase
        if ($response->isSuccessful() && $response->headers->get('Content-Type') === 'application/json') {
            $data = json_decode($response->getContent(), true);
            if ($data) {
                $transformedData = $this->transformKeysToCamelCase($data);
                $response->setContent(json_encode($transformedData));
            }
        }

        return $response;
    }

    /**
     * Transform keys of an array to camelCase.
     */
    private function transformKeysToCamelCase(array $data): array
    {
        $result = [];
        foreach ($data as $key => $value) {
            $camelKey = Str::camel($key);
            $result[$camelKey] = is_array($value) ? $this->transformKeysToCamelCase($value) : $value;
        }

        return $result;
    }
}
