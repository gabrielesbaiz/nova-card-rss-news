<?php

use Gabrielesbaiz\NovaCardRssNews\Presets;

it('merges the package config into the application', function (): void {
    expect(config('nova-card-rss-news.sources'))->toBeArray()->not->toBeEmpty()
        ->and(config('nova-card-rss-news.cache.ttl'))->toBe(300)
        ->and(config('nova-card-rss-news.http.timeout'))->toBe(8);
});

it('enables only the neutral starter preset out of the box', function (): void {
    $config = require __DIR__.'/../../config/nova-card-rss-news.php';

    expect($config['sources'])->toBe([Presets\StarterPreset::class]);
});

it('keeps every shipped preset well formed', function (): void {
    $presets = [
        Presets\StarterPreset::class,
        Presets\ItNewsPreset::class,
        Presets\ItInsurancePreset::class,
        Presets\ItMotoriPreset::class,
        Presets\ItEconomyPreset::class,
        Presets\ItTravelPreset::class,
        Presets\ItSportPreset::class,
        Presets\AggregatorsPreset::class,
    ];

    foreach ($presets as $preset) {
        $sources = (new $preset)->sources();

        expect($sources)->not->toBeEmpty();

        foreach ($sources as $source) {
            expect($source->url)->toStartWith('http')
                ->and($source->key)->toMatch('/^[a-z0-9_]+$/')
                ->and($source->label())->not->toBeEmpty()
                ->and($source->categoryLabel())->not->toBeEmpty();
        }
    }
});

it('does not lose any v2 source when carving the presets', function (): void {
    $keys = collect([
        Presets\ItNewsPreset::class,
        Presets\ItInsurancePreset::class,
        Presets\ItMotoriPreset::class,
        Presets\ItEconomyPreset::class,
        Presets\ItTravelPreset::class,
        Presets\ItSportPreset::class,
        Presets\AggregatorsPreset::class,
    ])->flatMap(fn (string $preset): array => (new $preset)->sources()->pluck('key')->all());

    expect($keys)->toContain('motor1', 'ansa_motori', 'ivass', 'ania', 'assinews', 'gazzetta', 'google_news_it', 'si_viaggia')
        ->and($keys->duplicates())->toBeEmpty();
});

it('registers the package translations under its own namespace', function (): void {
    expect(trans('nova-card-rss-news::card.stream'))->toBe('Latest news')
        ->and(trans('nova-card-rss-news::sources.world'))->toBe('World');

    app()->setLocale('it');

    expect(trans('nova-card-rss-news::card.stream'))->toBe('Ultime notizie')
        ->and(trans('nova-card-rss-news::sources.world'))->toBe('Mondo');
});

it('ships a json translation file for every bundled locale', function (): void {
    foreach (['en', 'it'] as $locale) {
        $path = __DIR__."/../../resources/lang/{$locale}.json";

        expect(is_file($path))->toBeTrue()
            ->and(json_decode((string) file_get_contents($path), true))->toBeArray();
    }
});
