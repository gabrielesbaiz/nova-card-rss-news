<?php

use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->useSources([
        'alpha' => ['title' => 'Alpha', 'url' => 'https://alpha.test/feed.xml'],
        'beta' => ['title' => 'Beta', 'url' => 'https://beta.test/feed.xml'],
    ]);
});

it('reports healthy sources and exits zero', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200)]);

    $this->artisan('nova-rss:check')
        ->expectsOutputToContain('alpha')
        ->assertSuccessful();
});

it('exits non-zero when a source is broken', function (): void {
    Http::fake([
        'alpha.test/*' => Http::response(fixture('sample-feed.xml'), 200),
        'beta.test/*' => Http::response('boom', 500),
    ]);

    $this->artisan('nova-rss:check')->assertFailed();
});

it('can check a single source', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200)]);

    $this->artisan('nova-rss:check --source=alpha')->assertSuccessful();

    Http::assertSentCount(1);
});

it('warms the cache for every source', function (): void {
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200)]);

    $this->artisan('nova-rss:warm')->assertSuccessful();

    Http::assertSentCount(2);

    // The dashboard request that follows is served from the cache.
    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha')->assertOk();
    Http::assertSentCount(2);
});

it('exports the catalogue to a file', function (): void {
    $path = sys_get_temp_dir().'/nova-rss-test-'.uniqid().'.opml';

    $this->artisan("nova-rss:export --output={$path}")->assertSuccessful();

    expect(file_get_contents($path))
        ->toContain('xmlUrl="https://alpha.test/feed.xml"')
        ->toContain('xmlUrl="https://beta.test/feed.xml"');

    unlink($path);
});

it('imports an OPML file into a pastable config block', function (): void {
    $path = sys_get_temp_dir().'/nova-rss-import-'.uniqid().'.opml';
    file_put_contents($path, <<<'XML'
    <?xml version="1.0" encoding="UTF-8"?>
    <opml version="2.0"><head><title>x</title></head><body>
      <outline text="Dev"><outline type="rss" text="Laravel News" xmlUrl="https://feed.laravel-news.com/" /></outline>
    </body></opml>
    XML);

    $this->artisan("nova-rss:import {$path}")
        ->expectsOutputToContain("'laravel_news' => ['title' => 'Laravel News', 'url' => 'https://feed.laravel-news.com/']")
        ->assertSuccessful();

    unlink($path);
});

it('discovers a feed from a plain site url', function (): void {
    Http::fake([
        'example.test' => Http::response('<link rel="alternate" type="application/rss+xml" href="https://example.test/feed.xml">', 200),
        'example.test/feed.xml' => Http::response(fixture('sample-feed.xml'), 200),
    ]);

    $this->artisan('nova-rss:discover https://example.test')
        ->expectsOutputToContain('https://example.test/feed.xml')
        ->assertSuccessful();
});

it('fails discovery when there is no feed', function (): void {
    Http::fake(['*' => Http::response('<html></html>', 200)]);

    $this->artisan('nova-rss:discover https://example.test')->assertFailed();
});
