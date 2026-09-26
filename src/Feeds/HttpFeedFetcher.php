<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds;

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Data\FetchResult;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedUnreachable;
use Illuminate\Http\Client\Factory;
use Throwable;

/**
 * Fetches feeds over HTTP with sane timeouts, a real user agent and
 * conditional-GET support so a refresh is usually a 304.
 */
final class HttpFeedFetcher implements FeedFetcher
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly Factory $http,
        private readonly array $config = [],
    ) {}

    public function fetch(string $url, ?string $etag = null, ?string $lastModified = null): FetchResult
    {
        $headers = [
            'User-Agent' => (string) ($this->config['user_agent'] ?? 'Mozilla/5.0 (compatible; NovaCardRssNews/3.0; +https://github.com/gabrielesbaiz/nova-card-rss-news)'),
            'Accept' => 'application/rss+xml, application/atom+xml, application/feed+json, application/xml;q=0.9, text/xml;q=0.9, */*;q=0.8',
            'Accept-Encoding' => 'gzip, deflate',
        ];

        if ($etag !== null && $etag !== '') {
            $headers['If-None-Match'] = $etag;
        }

        if ($lastModified !== null && $lastModified !== '') {
            $headers['If-Modified-Since'] = $lastModified;
        }

        $startedAt = microtime(true);

        try {
            $response = $this->http
                ->withHeaders($headers)
                ->withOptions([
                    'allow_redirects' => ['max' => (int) ($this->config['max_redirects'] ?? 5), 'strict' => true],
                    'decode_content' => true,
                    'verify' => (bool) ($this->config['verify'] ?? true),
                ])
                ->connectTimeout((int) ($this->config['connect_timeout'] ?? 3))
                ->timeout((int) ($this->config['timeout'] ?? 8))
                ->retry(
                    (int) ($this->config['retries'] ?? 2),
                    (int) ($this->config['retry_delay'] ?? 200),
                    throw: false,
                )
                ->get($url);
        } catch (Throwable $e) {
            throw FeedUnreachable::transport($url, $e);
        }

        $duration = round((microtime(true) - $startedAt) * 1000, 2);

        if ($response->status() !== 304 && $response->failed()) {
            throw FeedUnreachable::status($url, $response->status());
        }

        return new FetchResult(
            url: $url,
            status: $response->status(),
            body: $response->status() === 304 ? '' : $response->body(),
            etag: $response->header('ETag') ?: null,
            lastModified: $response->header('Last-Modified') ?: null,
            durationMs: $duration,
        );
    }
}
