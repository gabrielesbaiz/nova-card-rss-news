<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

/**
 * Cache-bypassing requests (?fresh=1) hit upstream feeds directly, so they get
 * their own, much smaller budget than ordinary cached reads.
 */
class ThrottleFreshFeed
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->boolean('fresh')) {
            return $next($request);
        }

        [$attempts, $minutes] = $this->budget();

        $key = 'nova-card-rss-news:fresh:'.($request->user()?->getAuthIdentifier() ?? $request->ip());

        if (RateLimiter::tooManyAttempts($key, $attempts)) {
            return response()->json([
                'message' => 'Too many refreshes. Try again shortly.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429);
        }

        RateLimiter::hit($key, $minutes * 60);

        return $next($request);
    }

    /**
     * @return array{int, int}
     */
    private function budget(): array
    {
        $configured = (string) config('nova-card-rss-news.routes.fresh_throttle', '20,1');
        [$attempts, $minutes] = array_pad(explode(',', $configured), 2, '1');

        return [max(1, (int) $attempts), max(1, (int) $minutes)];
    }
}
