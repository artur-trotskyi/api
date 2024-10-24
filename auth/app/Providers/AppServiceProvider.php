<?php

namespace App\Providers;

use App\Enums\AuthDriverEnum;
use App\Enums\TokenAbilityEnum;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
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
        if (config('auth.auth_driver') === AuthDriverEnum::SANCTUM->message()) {
            $this->overrideSanctumConfigurationToSupportRefreshToken();
        }
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

    /**
     * Override Sanctum's default configuration to support handling both access tokens and refresh tokens.
     *
     * @return void
     */
    private function overrideSanctumConfigurationToSupportRefreshToken(): void
    {
        Sanctum::$accessTokenAuthenticationCallback = function ($accessToken, $isValid) {
            $abilities = collect($accessToken->abilities);
            if (!empty($abilities) && $abilities[0] === TokenAbilityEnum::ISSUE_ACCESS_TOKEN->message()) {
                return $accessToken->expires_at && $accessToken->expires_at->isFuture();
            }

            return $isValid;
        };

        Sanctum::$accessTokenRetrievalCallback = function ($request) {
            if (!$request->routeIs('auth.refresh')) {
                return str_replace('Bearer ', '', $request->headers->get('Authorization'));
            }

            return $request->cookie('refreshToken') ?? '';
        };
    }
}
