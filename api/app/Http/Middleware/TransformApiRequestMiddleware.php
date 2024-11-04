<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class TransformApiRequestMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Transform keys of requests that are not GET to snake_case
        if ($request->method() !== 'GET') {
            $input = $request->all();
            $transformedInput = $this->transformKeysToSnakeCase($input);
            $request->replace($transformedInput);
        }

        return $next($request);
    }

    /**
     * Transform keys of an array to snake_case.
     */
    private function transformKeysToSnakeCase(array $input): array
    {
        $result = [];
        foreach ($input as $key => $value) {
            $snakeKey = Str::snake($key);
            $result[$snakeKey] = is_array($value) ? $this->transformKeysToSnakeCase($value) : $value;
        }

        return $result;
    }
}
