<?php

use Gabrielesbaiz\NovaCardRssNews\Http\Middleware\AuthorizeRssCard;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->useSources(['alpha' => ['title' => 'Alpha', 'url' => 'https://alpha.test/feed.xml']]);
    Http::fake(['*' => Http::response(fixture('sample-feed.xml'), 200)]);
});

it('blocks the endpoints when the configured gate denies', function (): void {
    config()->set('nova-card-rss-news.routes.authorize', fn (): bool => false);
    config()->set('nova-card-rss-news.routes.middleware', [
        AuthorizeRssCard::class,
    ]);
    $this->refreshRoutes();

    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha')->assertForbidden();
});

it('allows the endpoints when the gate passes', function (): void {
    config()->set('nova-card-rss-news.routes.authorize', fn (): bool => true);
    config()->set('nova-card-rss-news.routes.middleware', [
        AuthorizeRssCard::class,
    ]);
    $this->refreshRoutes();

    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha')->assertOk();
});

it('throttles cache-bypassing refreshes harder than cached reads', function (): void {
    config()->set('nova-card-rss-news.routes.fresh_throttle', '2,1');
    $this->refreshRoutes();

    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha&fresh=1')->assertOk();
    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha&fresh=1')->assertOk();
    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha&fresh=1')->assertStatus(429);

    // Ordinary cached reads are unaffected.
    $this->getJson('/nova-vendor/nova-card-rss-news/feed?source=alpha')->assertOk();
});

it('defaults to the nova middleware group', function (): void {
    $config = require __DIR__.'/../../config/nova-card-rss-news.php';

    expect($config['routes']['middleware'])->toBe(['nova'])
        ->and($config['routes']['authorize'])->toBeNull();
});
