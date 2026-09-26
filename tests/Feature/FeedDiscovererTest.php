<?php

use Gabrielesbaiz\NovaCardRssNews\Sources\FeedDiscoverer;
use Illuminate\Support\Facades\Http;

it('finds the feed declared in a page head', function (): void {
    Http::fake([
        'example.test' => Http::response('<html><head><link rel="alternate" type="application/rss+xml" href="/feed.xml" /></head></html>', 200),
        'example.test/feed.xml' => Http::response(fixture('sample-feed.xml'), 200),
    ]);

    $found = app(FeedDiscoverer::class)->discover('https://example.test');

    expect($found)->toHaveCount(1)
        ->and($found[0]['url'])->toBe('https://example.test/feed.xml')
        ->and($found[0]['parser'])->toBe('rss2')
        ->and($found[0]['title'])->toBe('Fake Feed')
        ->and($found[0]['items'])->toBe(2);
});

it('resolves absolute and protocol-relative hrefs', function (): void {
    Http::fake([
        'example.test' => Http::response('<link rel="alternate" type="application/atom+xml" href="//cdn.test/atom.xml">', 200),
        'cdn.test/atom.xml' => Http::response(fixture('sample-atom-feed.xml'), 200),
    ]);

    expect(app(FeedDiscoverer::class)->discover('https://example.test')[0]['url'])
        ->toBe('https://cdn.test/atom.xml');
});

it('probes the conventional paths when the page declares nothing', function (): void {
    Http::fake([
        'example.test' => Http::response('<html><body>no link tags</body></html>', 200),
        'example.test/feed' => Http::response(fixture('sample-feed.xml'), 200),
        '*' => Http::response('', 404),
    ]);

    $found = app(FeedDiscoverer::class)->discover('example.test');

    expect($found)->not->toBeEmpty()
        ->and($found[0]['url'])->toBe('https://example.test/feed');
});

it('recognises a url that is already a feed', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-json-feed.json'), 200)]);

    $found = app(FeedDiscoverer::class)->discover('https://example.test/feed.json');

    expect($found[0]['parser'])->toBe('json');
});

it('returns nothing when no feed can be found', function (): void {
    Http::fake(['*' => Http::response('<html></html>', 200)]);

    expect(app(FeedDiscoverer::class)->discover('https://example.test'))->toBe([]);
});
