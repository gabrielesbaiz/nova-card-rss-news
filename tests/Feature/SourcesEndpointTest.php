<?php

use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\NewsController;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\SourcesController;
use Illuminate\Http\Request;

it('returns the configured categories grouped', function (): void {
    config()->set('nova-card-rss-news.categories', [
        'cat_a' => [
            'label' => 'Category A',
            'sources' => [
                'src_one' => ['title' => 'Source One', 'url' => 'https://example.test/one.xml'],
                'src_two' => ['title' => 'Source Two', 'url' => 'https://example.test/two.xml'],
            ],
        ],
        'cat_b' => [
            'label' => 'Category B',
            'sources' => [
                'src_three' => ['title' => 'Source Three', 'url' => 'https://example.test/three.xml'],
            ],
        ],
    ]);

    $response = (new SourcesController())->index();
    $payload = $response->getData(true);

    expect($payload['categories'])
        ->toBeArray()
        ->toHaveCount(2);

    expect($payload['categories'][0])
        ->toMatchArray(['key' => 'cat_a', 'label' => 'Category A']);

    expect($payload['categories'][0]['sources'])
        ->toHaveCount(2)
        ->and($payload['categories'][0]['sources'][0])
        ->toMatchArray(['name' => 'src_one', 'title' => 'Source One']);

    expect($payload['categories'][1]['sources'][0])
        ->toMatchArray(['name' => 'src_three', 'title' => 'Source Three']);
});

it('skips categories that have no sources', function (): void {
    config()->set('nova-card-rss-news.categories', [
        'empty_cat' => [
            'label' => 'Empty',
            'sources' => [],
        ],
        'cat_a' => [
            'label' => 'Has sources',
            'sources' => [
                'src_one' => ['title' => 'Source One', 'url' => 'https://example.test/one.xml'],
            ],
        ],
    ]);

    $response = (new SourcesController())->index();
    $payload = $response->getData(true);

    expect($payload['categories'])->toHaveCount(1)
        ->and($payload['categories'][0]['key'])->toBe('cat_a');
});

it('returns 404 when fetching news for an unknown source', function (): void {
    $request = Request::create('/news', 'GET', ['source_key' => 'this-does-not-exist']);

    $response = (new NewsController())->index($request);

    expect($response->getStatusCode())->toBe(404);
});
