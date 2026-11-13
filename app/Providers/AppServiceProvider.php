<?php

namespace App\Providers;

use App\Support\AskQuota;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('ask', function (Request $request) {
            $max = AskQuota::max();

            if ($max <= 0) {
                return Limit::none();
            }

            return Limit::perDay($max)
                ->by($request->ip().'|'.now()->toDateString())
                ->response(function (Request $request, array $headers) {
                    $opensAt = AskQuota::opensAt();
                    $headers['Retry-After'] = (string) max(1, $opensAt->getTimestamp() - now()->getTimestamp());
                    $headers['X-RateLimit-Reset'] = (string) $opensAt->getTimestamp();

                    return response()->json([
                        'message' => AskQuota::CLOSED_MESSAGE,
                        'opens_at' => $opensAt->toIso8601String(),
                    ], 429, $headers);
                });
        });
    }
}
