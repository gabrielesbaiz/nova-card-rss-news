<?php

use Gabrielesbaiz\NovaCardRssNews\Http\Middleware\AuthorizeRssCard;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->useSources(['alpha' => ['title' => 'Alpha', 'url' => 'https://alpha.test/feed.xml']]);
    Http::fake(['*' => Http::response(feed_fixture('sample-feed.xml'), 200)]);
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

it('authorises against the guard Nova actually uses', function (): void {
    // Nova can authenticate on its own guard; asking only for the default
    // guard's user finds nobody and every request fails the gate.
    config()->set('auth.guards.nova', ['driver' => 'session', 'provider' => 'users']);
    config()->set('nova.guard', 'nova');
    config()->set('nova-card-rss-news.routes.authorize', fn ($request): bool => $request->user() !== null);
    config()->set('nova-card-rss-news.routes.middleware', [
        AuthorizeRssCard::class,
    ]);
    $this->refreshRoutes();

    $user = new User;
    $user->forceFill(['id' => 1, 'email' => 'someone@example.test']);

    $this->actingAs($user, 'nova')
        ->getJson('/nova-vendor/nova-card-rss-news/sources')
        ->assertOk();
});

it('falls back to the default guard when Nova has none configured', function (): void {
    config()->set('nova.guard', null);
    config()->set('nova-card-rss-news.routes.authorize', fn ($request): bool => true);
    config()->set('nova-card-rss-news.routes.middleware', [
        AuthorizeRssCard::class,
    ]);
    $this->refreshRoutes();

    $this->getJson('/nova-vendor/nova-card-rss-news/sources')->assertOk();
});
