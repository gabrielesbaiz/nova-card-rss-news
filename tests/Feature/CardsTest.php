<?php

use Gabrielesbaiz\NovaCardRssNews\Cards\RssNewsCard;
use Gabrielesbaiz\NovaCardRssNews\Cards\RssNewsSelectCard;
use Gabrielesbaiz\NovaCardRssNews\Cards\RssNewsStreamCard;
use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNews;
use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsSelect;
use Illuminate\Support\Facades\Crypt;

it('exposes the expected component names', function (): void {
    expect((new RssNewsCard)->component())->toBe('nova-card-rss-news')
        ->and((new RssNewsSelectCard)->component())->toBe('nova-card-rss-news-select')
        ->and((new RssNewsStreamCard)->component())->toBe('nova-card-rss-news-stream');
});

it('seeds every card with the configured defaults', function (): void {
    config()->set('nova-card-rss-news.defaults.limit', 7);
    config()->set('nova-card-rss-news.defaults.layout', 'compact');

    expect((new RssNewsCard)->meta())
        ->toHaveKey('limit', 7)
        ->toHaveKey('layout', 'compact')
        ->toHaveKey('read_state', true)
        ->toHaveKey('favicons', 'none');
});

it('builds the fixed-source card fluently', function (): void {
    $card = (new RssNewsCard)
        ->source('laravel_news')
        ->limit(5)
        ->layout('grid')
        ->images()
        ->autoRefresh(120)
        ->searchable()
        ->heading('Dev news');

    expect($card->meta())
        ->toHaveKey('source_key', 'laravel_news')
        ->toHaveKey('limit', 5)
        ->toHaveKey('layout', 'grid')
        ->toHaveKey('images', true)
        ->toHaveKey('auto_refresh', 120)
        ->toHaveKey('search', true)
        ->toHaveKey('heading', 'Dev news');
});

it('encrypts inline feed urls into the card meta', function (): void {
    $card = (new RssNewsCard)->feed('https://example.test/feed.xml', 'Example');
    $meta = $card->meta();

    expect($meta['source_key'])->toBeNull()
        ->and($meta['feed'])->not->toContain('example.test');

    $payload = json_decode(Crypt::decryptString($meta['feed']), true);

    expect($payload['url'])->toBe('https://example.test/feed.xml')
        ->and($payload['title'])->toBe('Example');
});

it('configures the select card', function (): void {
    $card = (new RssNewsSelectCard)
        ->defaultSource('bbc_world')
        ->categories(['world', 'technology'])
        ->remember(false);

    expect($card->meta())
        ->toHaveKey('source_key', 'bbc_world')
        ->toHaveKey('categories', ['world', 'technology'])
        ->toHaveKey('remember', false);
});

it('configures the stream card', function (): void {
    $card = (new RssNewsStreamCard)
        ->sources(['bbc_world', 'hacker_news'])
        ->categories(['development'])
        ->limit(25);

    expect($card->meta())
        ->toHaveKey('sources', ['bbc_world', 'hacker_news'])
        ->toHaveKey('categories', ['development'])
        ->toHaveKey('limit', 25);

    expect($card->width)->toBe('full');
});

it('keeps the v2 class names working as deprecated aliases', function (): void {
    expect(new NovaCardRssNews)->toBeInstanceOf(RssNewsCard::class)
        ->and((new NovaCardRssNews)->source('motor1')->meta())->toHaveKey('source_key', 'motor1')
        ->and(new NovaCardRssNewsSelect)->toBeInstanceOf(RssNewsSelectCard::class)
        ->and((new NovaCardRssNewsSelect)->defaultSource('motor1')->meta())->toHaveKey('source_key', 'motor1');
});

it('supports Nova\'s static make(), width() and canSee()', function (): void {
    $card = RssNewsCard::make()
        ->source('laravel_news')
        ->width('1/3')
        ->canSee(fn (): bool => false);

    expect($card)->toBeInstanceOf(RssNewsCard::class)
        ->and($card->width)->toBe('1/3')
        ->and($card->authorizedToSee(request()))->toBeFalse();

    $serialized = $card->jsonSerialize();

    expect($serialized['component'])->toBe('nova-card-rss-news')
        ->and($serialized['width'])->toBe('1/3')
        ->and($serialized['source_key'])->toBe('laravel_news');
});
