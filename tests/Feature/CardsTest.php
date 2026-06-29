<?php

use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNews;
use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsSelect;

it('NovaCardRssNews exposes the correct component name', function (): void {
    $card = new NovaCardRssNews();

    expect($card->component())->toBe('nova-card-rss-news');
});

it('NovaCardRssNews ships with a default source meta', function (): void {
    $card = new NovaCardRssNews();

    expect($card->meta()['source_key'])->toBe('motor1');
});

it('NovaCardRssNews source() and limit() update the meta payload', function (): void {
    $card = (new NovaCardRssNews())
        ->source('ansa_motori')
        ->limit(7);

    expect($card->meta())
        ->toHaveKey('source_key', 'ansa_motori')
        ->toHaveKey('limit', 7);
});

it('NovaCardRssNewsSelect exposes the correct component name', function (): void {
    $card = new NovaCardRssNewsSelect();

    expect($card->component())->toBe('nova-card-rss-news-select');
});

it('NovaCardRssNewsSelect ships with a default source and limit', function (): void {
    $card = new NovaCardRssNewsSelect();

    expect($card->meta())
        ->toHaveKey('source_key', 'motor1')
        ->toHaveKey('limit', 10);
});

it('NovaCardRssNewsSelect defaultSource() and limit() update the meta payload', function (): void {
    $card = (new NovaCardRssNewsSelect())
        ->defaultSource('insurance_trade')
        ->limit(5);

    expect($card->meta())
        ->toHaveKey('source_key', 'insurance_trade')
        ->toHaveKey('limit', 5);
});
