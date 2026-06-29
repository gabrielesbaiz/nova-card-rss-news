<?php

use Gabrielesbaiz\NovaCardRssNews\RssFeedService;

it('returns null when the source name is unknown', function (): void {
    expect(RssFeedService::getRssFeed('this-source-does-not-exist'))->toBeNull();
});

it('returns the title and url for a configured source without hitting the network', function (): void {
    config()->set('nova-card-rss-news.categories', [
        'demo' => [
            'label' => 'Demo',
            'sources' => [
                'fake' => [
                    'title' => 'Fake Feed',
                    'url' => 'file://' . __DIR__ . '/../Fixtures/sample-feed.xml',
                ],
            ],
        ],
    ]);

    $result = RssFeedService::getRssFeed('fake');

    expect($result)
        ->toBeArray()
        ->toHaveKey('title', 'Fake Feed')
        ->toHaveKey('url')
        ->toHaveKey('feed');
});

it('decodes HTML entities in titles and descriptions', function (): void {
    config()->set('nova-card-rss-news.categories', [
        'demo' => [
            'label' => 'Demo',
            'sources' => [
                'fake' => [
                    'title' => 'Fake Feed',
                    'url' => 'file://' . __DIR__ . '/../Fixtures/sample-feed.xml',
                ],
            ],
        ],
    ]);

    $result = RssFeedService::getRssFeed('fake');
    $first = $result['feed'][0] ?? null;

    expect($first)->not->toBeNull()
        ->and($first['title'])->toContain("dell'auto")
        ->and($first['title'])->not->toContain('&#039;')
        ->and($first['description'])->not->toContain('<img');
});

it('strips images and html tags from the description', function (): void {
    config()->set('nova-card-rss-news.categories', [
        'demo' => [
            'label' => 'Demo',
            'sources' => [
                'fake' => [
                    'title' => 'Fake Feed',
                    'url' => 'file://' . __DIR__ . '/../Fixtures/sample-feed.xml',
                ],
            ],
        ],
    ]);

    $result = RssFeedService::getRssFeed('fake');
    $first = $result['feed'][0] ?? null;

    expect($first['description'])
        ->not->toContain('<img')
        ->not->toContain('<p>')
        ->not->toContain('</p>');
});
