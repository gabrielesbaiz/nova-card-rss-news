<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the optional `routes.authorize` gate / closure from the config.
 */
class AuthorizeRssCard
{
    public function handle(Request $request, Closure $next): Response
    {
        $authorize = config('nova-card-rss-news.routes.authorize');

        $allowed = match (true) {
            $authorize === null => true,
            $authorize instanceof Closure => (bool) $authorize($request),
            is_string($authorize) => Gate::forUser($request->user())->allows($authorize),
            default => true,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }
}
