<?php

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedUnreachable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

it('sends a user agent and accepts feed content types', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200)]);

    app(FeedFetcher::class)->fetch('https://example.test/feed.xml');

    Http::assertSent(function (Request $request): bool {
        return str_contains($request->header('User-Agent')[0], 'NovaCardRssNews')
            && str_contains($request->header('Accept')[0], 'application/rss+xml');
    });
});

it('sends conditional headers when validators are known', function (): void {
    Http::fake(['*' => Http::response('', 304)]);

    $result = app(FeedFetcher::class)->fetch('https://example.test/feed.xml', '"abc"', 'Mon, 29 Jun 2026 10:00:00 GMT');

    expect($result->notModified())->toBeTrue()
        ->and($result->body)->toBe('');

    Http::assertSent(fn (Request $request): bool => $request->header('If-None-Match')[0] === '"abc"'
        && $request->header('If-Modified-Since')[0] === 'Mon, 29 Jun 2026 10:00:00 GMT');
});

it('captures etag and last-modified from the response', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200, [
        'ETag' => '"v2"',
        'Last-Modified' => 'Mon, 29 Jun 2026 10:00:00 GMT',
    ])]);

    $result = app(FeedFetcher::class)->fetch('https://example.test/feed.xml');

    expect($result->etag)->toBe('"v2"')
        ->and($result->lastModified)->toBe('Mon, 29 Jun 2026 10:00:00 GMT')
        ->and($result->status)->toBe(200);
});

it('throws when the feed answers with an error status', function (): void {
    Http::fake(['*' => Http::response('nope', 403)]);

    app(FeedFetcher::class)->fetch('https://example.test/feed.xml');
})->throws(FeedUnreachable::class);

it('throws when the host cannot be reached', function (): void {
    Http::fake(fn () => throw new ConnectionException('timed out'));

    app(FeedFetcher::class)->fetch('https://example.test/feed.xml');
})->throws(FeedUnreachable::class);
