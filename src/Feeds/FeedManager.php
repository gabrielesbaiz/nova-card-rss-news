<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds;

use Carbon\CarbonImmutable;
use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Data\Feed;
use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;
use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Events\FeedFetched;
use Gabrielesbaiz\NovaCardRssNews\Events\FeedFetchFailed;
use Gabrielesbaiz\NovaCardRssNews\Jobs\RefreshFeed;
use Illuminate\Contracts\Cache\Factory as CacheFactory;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Throwable;

/**
 * The single entry point for reading a feed: cache → conditional fetch →
 * parse → normalize, with stale-while-revalidate on top.
 */
final class FeedManager
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly FeedFetcher $fetcher,
        private readonly ParserRegistry $parsers,
        private readonly CacheFactory $cache,
        private readonly Dispatcher $events,
        private readonly array $config = [],
    ) {}

    /**
     * Read one source, honouring the cache unless a fresh copy was asked for.
     */
    public function get(Source $source, ?int $limit = null, bool $fresh = false): Feed
    {
        $cached = $fresh ? null : $this->store()->get($this->cacheKey($source));

        if (is_array($cached) && ! $this->isSoftExpired($source, $cached)) {
            return $this->hydrate($source, $cached)->normalized($limit);
        }

        try {
            $feed = $this->refresh($source, is_array($cached) ? $cached : null);

            return $feed->normalized($limit);
        } catch (Throwable $e) {
            if (is_array($cached)) {
                $this->events->dispatch(new FeedFetchFailed($source, $e, servedStale: true));

                return $this->hydrate($source, $cached)->markStale()->normalized($limit);
            }

            $this->events->dispatch(new FeedFetchFailed($source, $e));

            throw $e;
        }
    }

    /**
     * Merge several sources into one chronologically sorted stream.
     *
     * @param  iterable<Source>  $sources
     */
    public function stream(iterable $sources, ?int $limit = null, bool $fresh = false): Feed
    {
        $items = [];
        $fetchedAt = null;
        $stale = false;

        foreach ($sources as $source) {
            try {
                $feed = $this->get($source, null, $fresh);
            } catch (Throwable) {
                continue;
            }

            foreach ($feed->items as $item) {
                $items[] = $item->forSource($source);
            }

            $stale = $stale || $feed->stale;
            $fetchedAt = $fetchedAt === null || ($feed->fetchedAt !== null && $feed->fetchedAt->greaterThan($fetchedAt))
                ? $feed->fetchedAt
                : $fetchedAt;
        }

        return (new Feed(
            key: 'stream',
            title: (string) trans('nova-card-rss-news::card.stream'),
            feedUrl: '',
            items: $items,
            fetchedAt: $fetchedAt ?? CarbonImmutable::now(),
            stale: $stale,
        ))->normalized($limit);
    }

    /**
     * Fetch, parse and cache a source. Returns the freshly stored feed.
     *
     * @param  array<string, mixed>|null  $cached
     */
    public function refresh(Source $source, ?array $cached = null): Feed
    {
        $result = $this->fetcher->fetch($source->url, $cached['etag'] ?? null, $cached['last_modified'] ?? null);

        if ($result->notModified() && $cached !== null) {
            $payload = array_merge($cached, ['fetched_at' => CarbonImmutable::now()->toIso8601String()]);
            $this->put($source, $payload);

            $feed = $this->hydrate($source, $payload);
            $this->events->dispatch(new FeedFetched($source, $feed, $result->durationMs, notModified: true));

            return $feed;
        }

        $parser = $this->parsers->resolve($result->body, $source->parser, $source->url);
        $parsed = $parser->parse($result->body);

        /** @var array<int, FeedItem> $items */
        $items = $parsed['items'];

        $payload = [
            'title' => $parsed['title'],
            'site_url' => $parsed['site_url'],
            'parser' => $parser->key(),
            'etag' => $result->etag,
            'last_modified' => $result->lastModified,
            'fetched_at' => CarbonImmutable::now()->toIso8601String(),
            'items' => array_map(
                static fn (FeedItem $item): array => $item->toArray(),
                array_map(static fn (FeedItem $item): FeedItem => $item->forSource($source), $items),
            ),
        ];

        $this->put($source, $payload);

        $feed = $this->hydrate($source, $payload);
        $this->events->dispatch(new FeedFetched($source, $feed, $result->durationMs));

        return $feed;
    }

    public function forget(Source $source): void
    {
        $this->store()->forget($this->cacheKey($source));
    }

    public function cacheKey(Source $source): string
    {
        $prefix = (string) ($this->config['cache']['prefix'] ?? 'nova-card-rss-news');

        return $prefix.':feed:'.sha1($source->key.'|'.$source->url);
    }

    /**
     * A cached entry past its soft TTL is still usable, but triggers a refresh.
     *
     * @param  array<string, mixed>  $cached
     */
    private function isSoftExpired(Source $source, array $cached): bool
    {
        $fetchedAt = isset($cached['fetched_at']) ? CarbonImmutable::parse((string) $cached['fetched_at']) : null;

        if ($fetchedAt === null) {
            return true;
        }

        $age = $fetchedAt->diffInSeconds(CarbonImmutable::now(), absolute: true);
        $ttl = $source->ttl ?? (int) ($this->config['cache']['ttl'] ?? 300);

        if ($age <= $ttl) {
            return false;
        }

        // Past the soft TTL: serve the stale copy and revalidate in the
        // background when a queue is configured, otherwise refresh inline.
        if ($this->queueEnabled()) {
            // add() is atomic, so only the first request past the soft TTL
            // queues a refresh; the rest just serve the stale copy.
            if ($this->store()->add($this->cacheKey($source).':refreshing', true, 60)) {
                RefreshFeed::dispatch($source)
                    ->onConnection($this->config['queue']['connection'] ?? null)
                    ->onQueue($this->config['queue']['queue'] ?? null);
            }

            return false;
        }

        return true;
    }

    private function queueEnabled(): bool
    {
        return (bool) ($this->config['queue']['enabled'] ?? false);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function put(Source $source, array $payload): void
    {
        $ttl = $source->ttl ?? (int) ($this->config['cache']['ttl'] ?? 300);
        $stale = (int) ($this->config['cache']['stale_ttl'] ?? 3600);

        $this->store()->put($this->cacheKey($source), $payload, $ttl + $stale);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function hydrate(Source $source, array $payload): Feed
    {
        return new Feed(
            key: $source->key,
            title: $source->label(),
            feedUrl: $source->url,
            items: array_map(
                static fn (array $item): FeedItem => FeedItem::fromArray($item),
                array_values((array) ($payload['items'] ?? [])),
            ),
            siteUrl: $payload['site_url'] ?? $source->site(),
            fetchedAt: isset($payload['fetched_at']) ? CarbonImmutable::parse((string) $payload['fetched_at']) : null,
            parser: $payload['parser'] ?? null,
        );
    }

    private function store(): Repository
    {
        return $this->cache->store($this->config['cache']['store'] ?? null);
    }
}
