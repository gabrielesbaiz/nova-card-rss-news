<?php

it('merges the package config into the application', function (): void {
    expect(config('nova-card-rss-news.categories'))
        ->toBeArray()
        ->not->toBeEmpty();
});

it('exposes the expected category keys', function (): void {
    $keys = array_keys(config('nova-card-rss-news.categories'));

    expect($keys)->toContain(
        'assicurazioni_specializzate',
        'assicurazioni_istituzioni',
        'assicurazioni_economiche',
        'motori',
        'economia',
        'quotidiani',
        'agenzie_stampa',
        'sport',
        'aggregatori',
    );
});

it('shapes every source with a title and url', function (): void {
    $categories = config('nova-card-rss-news.categories');

    foreach ($categories as $key => $category) {
        expect($category)->toHaveKey('label')
            ->and($category)->toHaveKey('sources');

        foreach ($category['sources'] as $name => $source) {
            expect($source)
                ->toHaveKey('title')
                ->toHaveKey('url')
                ->and($source['url'])->toStartWith('http');
        }
    }
});

it('keeps backwards-compatible motori source keys', function (): void {
    $motori = config('nova-card-rss-news.categories.motori.sources');

    expect(array_keys($motori))->toContain(
        'motor1',
        'alvolante',
        'ansa_motori',
        'corriere_motori',
        'il_sole_24_ore_motori',
        'motori_it',
    );
});
