<?php

use Carbon\CarbonImmutable;
use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Events\FeedFetched;
use Gabrielesbaiz\NovaCardRssNews\Events\FeedFetchFailed;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedUnreachable;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;

function demoSource(array $overrides = []): Source
{
    return new Source(
        key: $overrides['key'] ?? 'demo',
        title: $overrides['title'] ?? 'Demo Feed',
        url: $overrides['url'] ?? 'https://example.test/feed.xml',
        ttl: $overrides['ttl'] ?? null,
    );
}

it('fetches, normalizes and caches a feed', function (): void {
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200, ['ETag' => '"v1"'])]);

    $feed = app(FeedManager::class)->get(demoSource());

    expect($feed->items)->toHaveCount(2)
        ->and($feed->parser)->toBe('rss2')
        ->and($feed->stale)->toBeFalse()
        // Newest first, regardless of the order in the document.
        ->and($feed->items[0]->link)->toBe('https://example.test/news/2')
        ->and($feed->items[0]->sourceKey)->toBe('demo');

    app(FeedManager::class)->get(demoSource());

    Http::assertSentCount(1);
});

it('applies the limit server side', function (): void {
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);

    expect(app(FeedManager::class)->get(demoSource(), 1)->items)->toHaveCount(1);
});

it('bypasses the cache when a fresh copy is requested', function (): void {
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);

    $manager = app(FeedManager::class);
    $first = $manager->get(demoSource());
    CarbonImmutable::setTestNow(CarbonImmutable::now()->addSecond());
    $second = $manager->get(demoSource(), null, fresh: true);

    Http::assertSentCount(2);
    expect($second->fetchedAt?->greaterThan($first->fetchedAt))->toBeTrue();

    CarbonImmutable::setTestNow();
});

it('revalidates with a conditional get and keeps the cached items on 304', function (): void {
    Http::fakeSequence()
        ->push(feed_fixture('sample-feed.xml'), 200, ['ETag' => '"v1"'])
        ->push('', 304);

    $manager = app(FeedManager::class);
    $manager->get(demoSource());

    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes(10));

    $feed = $manager->get(demoSource());

    expect($feed->items)->toHaveCount(2)
        ->and($feed->stale)->toBeFalse();

    Http::assertSentCount(2);

    CarbonImmutable::setTestNow();
});

it('serves the stale copy when a refresh fails', function (): void {
    Event::fake([FeedFetchFailed::class]);

    Http::fakeSequence()
        ->push(feed_fixture('sample-feed.xml'), 200)
        ->push('boom', 500)
        ->push('boom', 500)
        ->push('boom', 500);

    $manager = app(FeedManager::class);
    $manager->get(demoSource());

    CarbonImmutable::setTestNow(CarbonImmutable::now()->addMinutes(10));

    $feed = $manager->get(demoSource());

    expect($feed->stale)->toBeTrue()
        ->and($feed->items)->toHaveCount(2);

    Event::assertDispatched(FeedFetchFailed::class, fn (FeedFetchFailed $e): bool => $e->servedStale);

    CarbonImmutable::setTestNow();
});

it('throws when the feed fails and nothing is cached', function (): void {
    Http::fake(['*' => Http::response('boom', 500)]);

    app(FeedManager::class)->get(demoSource());
})->throws(FeedUnreachable::class);

it('honours a per-source ttl', function (): void {
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);

    $manager = app(FeedManager::class);
    $manager->get(demoSource(['ttl' => 60]));

    CarbonImmutable::setTestNow(CarbonImmutable::now()->addSeconds(90));
    $manager->get(demoSource(['ttl' => 60]));

    Http::assertSentCount(2);

    CarbonImmutable::setTestNow();
});

it('dispatches FeedFetched with the parse duration', function (): void {
    Event::fake([FeedFetched::class]);
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);

    app(FeedManager::class)->get(demoSource());

    Event::assertDispatched(FeedFetched::class, fn (FeedFetched $e): bool => $e->source->key === 'demo' && ! $e->notModified);
});

it('merges several sources into one chronological stream', function (): void {
    Http::fake([
        'a.test/*' => Http::response(feed_fixture('sample-feed.xml'), 200),
        'b.test/*' => Http::response(feed_fixture('sample-rich-feed.xml'), 200),
    ]);

    $feed = app(FeedManager::class)->stream([
        demoSource(['key' => 'a', 'url' => 'https://a.test/feed.xml']),
        demoSource(['key' => 'b', 'title' => 'Rich', 'url' => 'https://b.test/feed.xml']),
    ]);

    expect($feed->items)->toHaveCount(5)
        ->and(collect($feed->items)->pluck('sourceKey')->unique()->sort()->values()->all())->toBe(['a', 'b']);

    $timestamps = array_map(fn ($item): int => $item->timestamp(), $feed->items);
    expect($timestamps)->toBe(collect($timestamps)->sortDesc()->values()->all());
});

it('skips unreachable sources in a stream instead of failing', function (): void {
    Http::fake([
        'a.test/*' => Http::response(feed_fixture('sample-feed.xml'), 200),
        'b.test/*' => Http::response('boom', 500),
    ]);

    $feed = app(FeedManager::class)->stream([
        demoSource(['key' => 'a', 'url' => 'https://a.test/feed.xml']),
        demoSource(['key' => 'b', 'url' => 'https://b.test/feed.xml']),
    ]);

    expect($feed->items)->toHaveCount(2);
});

it('deduplicates identical items', function (): void {
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);

    $feed = app(FeedManager::class)->stream([
        demoSource(['key' => 'a']),
        demoSource(['key' => 'b']),
    ]);

    expect($feed->items)->toHaveCount(2);
});
