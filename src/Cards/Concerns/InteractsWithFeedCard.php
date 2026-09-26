<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Cards\Concerns;

use Illuminate\Support\Facades\Crypt;

/**
 * Fluent options shared by every RSS card.
 */
trait InteractsWithFeedCard
{
    /**
     * Maximum number of items to render (applied server side).
     */
    public function limit(int $limit): static
    {
        return $this->withMeta(['limit' => max(1, $limit)]);
    }

    /**
     * hero | compact | grid | ticker
     */
    public function layout(string $layout): static
    {
        return $this->withMeta(['layout' => $layout]);
    }

    /**
     * Show (or hide) item thumbnails when the feed provides them.
     */
    public function images(bool $images = true): static
    {
        return $this->withMeta(['images' => $images]);
    }

    /**
     * Re-fetch every N seconds. Paused while the tab is hidden. 0 disables.
     */
    public function autoRefresh(int $seconds): static
    {
        return $this->withMeta(['auto_refresh' => max(0, $seconds)]);
    }

    /**
     * Render the client-side search box.
     */
    public function searchable(bool $searchable = true): static
    {
        return $this->withMeta(['search' => $searchable]);
    }

    /**
     * Track read / unread items in the browser.
     */
    public function readState(bool $readState = true): static
    {
        return $this->withMeta(['read_state' => $readState]);
    }

    /**
     * Override the card heading.
     */
    public function heading(string $heading): static
    {
        return $this->withMeta(['heading' => $heading]);
    }

    /**
     * Use a feed that is not in the catalogue. The URL is encrypted into the
     * card meta so the endpoint will never fetch a client-supplied host.
     */
    public function feed(string $url, ?string $title = null, ?string $key = null): static
    {
        return $this->withMeta([
            'source_key' => null,
            'feed' => Crypt::encryptString((string) json_encode([
                'key' => $key ?? 'inline_'.substr(sha1($url), 0, 8),
                'title' => $title ?? $url,
                'url' => $url,
            ])),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function defaultMeta(): array
    {
        $defaults = (array) config('nova-card-rss-news.defaults', []);

        return [
            'limit' => (int) ($defaults['limit'] ?? 10),
            'layout' => (string) ($defaults['layout'] ?? 'hero'),
            'auto_refresh' => (int) ($defaults['auto_refresh'] ?? 0),
            'search' => (bool) ($defaults['search'] ?? false),
            'read_state' => (bool) ($defaults['read_state'] ?? true),
            'images' => (bool) (config('nova-card-rss-news.ui.images', true)),
            'favicons' => (string) config('nova-card-rss-news.ui.favicons', 'google'),
        ];
    }
}
