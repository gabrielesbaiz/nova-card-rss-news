<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Sources;

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Feeds\ParserRegistry;
use Throwable;

/**
 * Finds the feed for a plain website URL: read <link rel="alternate">, then
 * fall back to probing the handful of conventional paths.
 */
final class FeedDiscoverer
{
    private const PROBES = ['/feed', '/feed/', '/rss', '/rss.xml', '/atom.xml', '/index.xml', '/feed.json'];

    public function __construct(
        private readonly FeedFetcher $fetcher,
        private readonly ParserRegistry $parsers,
    ) {}

    /**
     * @return array<int, array{url: string, title: ?string, parser: string, items: int}>
     */
    public function discover(string $url): array
    {
        $url = str_starts_with($url, 'http') ? $url : 'https://'.$url;

        $candidates = [];

        try {
            $response = $this->fetcher->fetch($url);
            $candidates = $this->fromHtml($response->body, $url);

            // The URL may already be a feed.
            if ($this->validate($url) !== null) {
                array_unshift($candidates, $url);
            }
        } catch (Throwable) {
            // Unreachable page: fall through to the conventional paths.
        }

        if ($candidates === []) {
            $base = rtrim($this->origin($url), '/');

            foreach (self::PROBES as $path) {
                $candidates[] = $base.$path;
            }
        }

        $found = [];

        foreach (array_unique($candidates) as $candidate) {
            $result = $this->validate($candidate);

            if ($result !== null) {
                $found[] = $result;
            }
        }

        return $found;
    }

    /**
     * @return array{url: string, title: ?string, parser: string, items: int}|null
     */
    public function validate(string $url): ?array
    {
        try {
            $response = $this->fetcher->fetch($url);
            $parser = $this->parsers->resolve($response->body, null, $url);
            $parsed = $parser->parse($response->body);

            if ($parsed['items'] === []) {
                return null;
            }

            return [
                'url' => $url,
                'title' => $parsed['title'],
                'parser' => $parser->key(),
                'items' => count($parsed['items']),
            ];
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, string>
     */
    private function fromHtml(string $html, string $baseUrl): array
    {
        if (! preg_match_all('/<link\b[^>]*>/i', $html, $matches)) {
            return [];
        }

        $links = [];

        foreach ($matches[0] as $tag) {
            if (! preg_match('/type=["\']?(application\/(rss|atom)\+xml|application\/feed\+json)/i', $tag)) {
                continue;
            }

            if (! preg_match('/href=["\']([^"\']+)["\']/i', $tag, $href)) {
                continue;
            }

            $links[] = $this->absolute(html_entity_decode($href[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), $baseUrl);
        }

        return array_values(array_filter($links));
    }

    private function absolute(string $href, string $baseUrl): string
    {
        if (str_starts_with($href, 'http')) {
            return $href;
        }

        if (str_starts_with($href, '//')) {
            return (parse_url($baseUrl, PHP_URL_SCHEME) ?: 'https').':'.$href;
        }

        return rtrim($this->origin($baseUrl), '/').'/'.ltrim($href, '/');
    }

    private function origin(string $url): string
    {
        $parts = parse_url($url);

        return ($parts['scheme'] ?? 'https').'://'.($parts['host'] ?? '').(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
