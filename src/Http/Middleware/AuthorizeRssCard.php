<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Authenticatable;
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
            is_string($authorize) => Gate::forUser($this->user($request))->allows($authorize),
            default => true,
        };

        abort_unless($allowed, 403);

        return $next($request);
    }

    /**
     * Nova may authenticate on its own guard — `nova` in a default install is
     * `web`, but applications change it. Asking $request->user() alone finds
     * the *default* guard's user, which is null in that case, and every
     * request then fails the gate.
     */
    protected function user(Request $request): ?Authenticatable
    {
        $guard = config('nova.guard');

        return ($guard ? $request->user($guard) : null) ?? $request->user();
    }
}
