<?php

use Illuminate\Support\Facades\Artisan;

/*
 * nova-rss:list answers one question — "what do I put in defaultSource()?" —
 * so the keys have to come out in a form you can paste without editing.
 */

beforeEach(function (): void {
    $this->useSources([
        'zeta' => ['title' => 'Zeta Press', 'url' => 'https://zeta.test/feed'],
        'alpha' => ['title' => 'Alpha Wire', 'url' => 'https://alpha.test/feed'],
    ], 'news');
});

it('lists every key with its title and category', function (): void {
    Artisan::call('nova-rss:list');
    $output = Artisan::output();

    expect($output)
        ->toContain('alpha')->toContain('Alpha Wire')
        ->toContain('zeta')->toContain('Zeta Press')
        ->toContain('2 source(s)');
});

it('orders by category then title, the way the picker does', function (): void {
    Artisan::call('nova-rss:list');
    $output = Artisan::output();

    expect(strpos($output, 'Alpha Wire'))->toBeLessThan(strpos($output, 'Zeta Press'));
});

it('prints bare keys for copying', function (): void {
    Artisan::call('nova-rss:list', ['--keys' => true]);

    expect(trim(Artisan::output()))->toBe("alpha\nzeta");
});

it('prints paste-ready defaultSource() calls', function (): void {
    Artisan::call('nova-rss:list', ['--calls' => true]);
    $output = Artisan::output();

    expect($output)
        ->toContain("->defaultSource('alpha')")
        ->toContain('// Alpha Wire')
        ->toContain("->defaultSource('zeta')");
});

it('can write source() calls instead', function (): void {
    Artisan::call('nova-rss:list', ['--calls' => true, '--method' => 'source']);

    expect(Artisan::output())
        ->toContain("->source('alpha')")
        ->not->toContain('defaultSource');
});

it('filters by category and by search term', function (): void {
    $this->useSources([
        'motor1' => ['title' => 'Motor1', 'url' => 'https://motor1.test/feed', 'category' => 'motori'],
        'alpha' => ['title' => 'Alpha Wire', 'url' => 'https://alpha.test/feed', 'category' => 'news'],
    ], 'news');

    Artisan::call('nova-rss:list', ['--category' => ['motori'], '--keys' => true]);
    expect(trim(Artisan::output()))->toBe('motor1');

    Artisan::call('nova-rss:list', ['--search' => 'wire', '--keys' => true]);
    expect(trim(Artisan::output()))->toBe('alpha');
});

it('emits JSON when asked', function (): void {
    Artisan::call('nova-rss:list', ['--json' => true]);
    $decoded = json_decode(Artisan::output(), true);

    expect($decoded)->toBeArray()->toHaveCount(2)
        ->and($decoded[0])->toHaveKeys(['key', 'title', 'url', 'category']);
});

it('says so when nothing matches', function (): void {
    $code = Artisan::call('nova-rss:list', ['--search' => 'nothing-like-this']);

    expect($code)->toBe(1)
        ->and(Artisan::output())->toContain('No sources matched');
});
