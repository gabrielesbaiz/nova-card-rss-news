<?php

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\SourceNotFound;
use Gabrielesbaiz\NovaCardRssNews\Presets\ItMotoriPreset;
use Gabrielesbaiz\NovaCardRssNews\Presets\StarterPreset;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;

function repository(array $providers): SourceRepository
{
    return new SourceRepository(app(), $providers);
}

it('ships the starter preset by default', function (): void {
    $sources = app(SourceRepository::class)->all();

    expect($sources)->not->toBeEmpty()
        ->and($sources->has('laravel_news'))->toBeTrue()
        ->and($sources->get('laravel_news')->categoryKey)->toBe('development');
});

it('resolves preset classes, inline arrays, closures and source objects', function (): void {
    $repository = repository([
        StarterPreset::class,
        ['categories' => ['team' => ['label' => 'Team', 'sources' => [
            'blog' => ['title' => 'Blog', 'url' => 'https://example.test/feed'],
        ]]]],
        fn (): array => [new Source(key: 'closure', title: 'Closure feed', url: 'https://closure.test/feed')],
        new Source(key: 'object', title: 'Object feed', url: 'https://object.test/feed'),
    ]);

    expect($repository->all()->keys())
        ->toContain('laravel_news', 'blog', 'closure', 'object');
});

it('lets later providers override earlier ones by key', function (): void {
    $repository = repository([
        ['categories' => ['a' => ['sources' => ['dup' => ['title' => 'First', 'url' => 'https://first.test']]]]],
        ['categories' => ['b' => ['sources' => ['dup' => ['title' => 'Second', 'url' => 'https://second.test']]]]],
    ]);

    expect($repository->find('dup')->url)->toBe('https://second.test');
});

it('skips disabled sources and sources without a url', function (): void {
    $repository = repository([
        ['categories' => ['a' => ['sources' => [
            'off' => ['title' => 'Off', 'url' => 'https://off.test', 'enabled' => false],
            'empty' => ['title' => 'No url'],
            'on' => ['title' => 'On', 'url' => 'https://on.test'],
        ]]]],
    ]);

    expect($repository->all()->keys()->all())->toBe(['on']);
});

it('accepts the v2 config shape verbatim', function (): void {
    $repository = repository([[
        'categories' => ItMotoriPreset::categories(),
    ]]);

    expect($repository->find('motor1'))->not->toBeNull()
        ->and($repository->find('motor1')->categoryKey)->toBe('motori')
        ->and($repository->find('motor1')->categoryLabel())->toBe('Motori');
});

it('groups the catalogue for the picker and can be filtered by category', function (): void {
    $grouped = repository([StarterPreset::class])->grouped(['development']);

    expect($grouped)->toHaveCount(1)
        ->and($grouped[0]['key'])->toBe('development')
        ->and(collect($grouped[0]['sources'])->pluck('name'))->toContain('laravel_news');
});

it('resolves category labels through translations when a key is given', function (): void {
    $source = repository([StarterPreset::class])->find('bbc_world');

    // The starter preset uses translation keys, resolved from lang/en/sources.php.
    expect($source->categoryLabel())->toBe('World');
});

it('throws a helpful error for unknown keys', function (): void {
    app(SourceRepository::class)->findOrFail('nope');
})->throws(SourceNotFound::class);

it('selects sources by key and by category', function (): void {
    $repository = repository([StarterPreset::class]);

    expect($repository->only(['laravel_news', 'bbc_world'])->keys()->all())
        ->toBe(['bbc_world', 'laravel_news'])
        ->and($repository->inCategories(['technology'])->keys())
        ->toContain('hacker_news');
});
