<?php

namespace App\Providers;

use App\Http\Resources\ErrorResource;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
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
        $this->app->singleton(Client::class, function () {
            return ClientBuilder::create()
                ->setHosts(config('elasticsearch.hosts'))
                ->setBasicAuthentication(config('elasticsearch.user'), config('elasticsearch.pass'))
                ->build();
        });
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
                ? Limit::perMinute(100)->by($request->user()->id)
                : Limit::perMinute(30)->by($request->ip());

            return $limit->response(function ($request) {
                $errorData = ['errors' => ['Too Many Attempts']];
                $resource = new ErrorResource(
                    $errorData,
                    'Too Many Attempts',
                    Response::HTTP_TOO_MANY_REQUESTS
                );

                throw new HttpResponseException($resource->toResponse($request));
            });
        });
    }
}
