<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Response;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $limit = $request->user()
                ? Limit::perSecond(2)->by($request->user()->id)
                : Limit::perSecond(1)->by($request->ip());

            return $limit->response(function ($request) {
                $errorData = [
                    'message' => 'Too Many Attempts',
                    'errors' => ['Too Many Attempts'],
                ];

                throw new HttpResponseException(
                    response()->json($errorData, Response::HTTP_TOO_MANY_REQUESTS)
                );
            });
        });
    }
}
