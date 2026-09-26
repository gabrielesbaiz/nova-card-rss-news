<?php

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;

beforeEach(function (): void {
    $this->useSources([
        'alpha' => ['title' => 'Alpha', 'url' => 'https://alpha.test/feed.xml'],
        'beta' => ['title' => 'Beta', 'url' => 'https://beta.test/feed.xml'],
    ]);

    Http::fake([
        'alpha.test/*' => Http::response(fixture('sample-feed.xml'), 200),
        'beta.test/*' => Http::response(fixture('sample-rich-feed.xml'), 200),
    ]);
});

function endpoint(string $path): string
{
    return '/nova-vendor/nova-card-rss-news/'.ltrim($path, '/');
}

it('returns a normalized feed payload', function (): void {
    $response = $this->getJson(endpoint('feed?source=alpha'))->assertOk();

    $response->assertJsonStructure([
        'key', 'title', 'url', 'feed_url', 'fetched_at', 'stale', 'parser',
        'items' => [['id', 'title', 'link', 'summary', 'published_at', 'image_url', 'author', 'categories', 'source_key']],
    ]);

    expect($response->json('key'))->toBe('alpha')
        ->and($response->json('items.0.published_at'))->toContain('2026-06-29')
        ->and($response->json('items'))->toHaveCount(2);
});

it('applies the requested limit', function (): void {
    $this->getJson(endpoint('feed?source=alpha&limit=1'))
        ->assertOk()
        ->assertJsonCount(1, 'items');
});

it('actually re-fetches when fresh=1 is passed', function (): void {
    $this->getJson(endpoint('feed?source=alpha'))->assertOk();
    $this->getJson(endpoint('feed?source=alpha'))->assertOk();
    Http::assertSentCount(1);

    $this->getJson(endpoint('feed?source=alpha&fresh=1'))->assertOk();
    Http::assertSentCount(2);
});

it('404s for an unknown source', function (): void {
    $this->getJson(endpoint('feed?source=nope'))->assertNotFound();
});

it('502s when the upstream feed is broken and nothing is cached', function (): void {
    Http::fake(['gamma.test/*' => Http::response('boom', 500)]);
    $this->useSources(['gamma' => ['title' => 'Gamma', 'url' => 'https://gamma.test/feed.xml']]);

    $this->getJson(endpoint('feed?source=gamma'))->assertStatus(502);
});

it('accepts an encrypted inline feed url', function (): void {
    $payload = Crypt::encryptString((string) json_encode([
        'key' => 'inline', 'title' => 'Inline', 'url' => 'https://alpha.test/feed.xml',
    ]));

    $this->getJson(endpoint('feed?feed='.urlencode($payload)))
        ->assertOk()
        ->assertJsonPath('key', 'inline');
});

it('rejects a raw url that was not signed by the card', function (): void {
    $this->getJson(endpoint('feed?feed='.urlencode('https://evil.test/internal')))->assertNotFound();

    Http::assertNotSent(fn ($request): bool => str_contains($request->url(), 'evil.test'));
});

it('returns the grouped source catalogue', function (): void {
    $response = $this->getJson(endpoint('sources'))->assertOk();

    expect($response->json('categories.0.key'))->toBe('demo')
        ->and($response->json('categories.0.sources'))->toHaveCount(2);
});

it('merges several sources in the stream endpoint', function (): void {
    $response = $this->getJson(endpoint('stream?sources[]=alpha&sources[]=beta'))->assertOk();

    expect($response->json('items'))->toHaveCount(5)
        ->and(collect($response->json('items'))->pluck('source_key')->unique()->sort()->values()->all())
        ->toBe(['alpha', 'beta']);
});

it('accepts categories in the stream endpoint', function (): void {
    $this->getJson(endpoint('stream?categories[]=demo'))
        ->assertOk()
        ->assertJsonCount(5, 'items');
});

it('404s the stream when nothing known was requested', function (): void {
    $this->getJson(endpoint('stream?sources[]=nope'))->assertNotFound();
});

it('validates the query string', function (): void {
    $this->getJson(endpoint('feed?source=alpha&limit=999'))->assertStatus(422);
});

it('titles the stream from the package translations', function (): void {
    $response = $this->getJson(endpoint('stream?sources[]=alpha'))->assertOk();

    expect($response->json('title'))->toBe('Latest news');
});
