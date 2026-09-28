<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

/*
 * nova-rss:discover has to be useful on a "feed index" page — a page that
 * lists dozens of feeds and declares none of them. What it prints has to be
 * pastable, which means every feed, with keys and labels that tell them apart.
 */

function indexPage(): string
{
    return '<html><head><title>Rss del Corriere della Sera</title></head><body>'
        .'<a href="https://xml2.corriere.test/feed-hp/homepage.xml">Homepage</a>'
        .'<a href="/dynamic-feed/rss/section/Cronache.xml">Cronache</a>'
        .'<a href="/dynamic-feed/rss/section/Spettacoli-Cinema.xml">Cinema</a>'
        .'<a href="/notizie/index.shtml">Not a feed</a>'
        .'</body></html>';
}

/** The homepage feed names the paper; the section feeds share a generic title. */
function feedTitled(string $title): string
{
    return str_replace('<title>Fake Feed</title>', "<title>{$title}</title>", feed_fixture('sample-feed.xml'));
}

beforeEach(function (): void {
    Http::fake([
        'www.corriere.test/rss/*' => Http::response(indexPage(), 200),
        'www.corriere.test/rss/' => Http::response(indexPage(), 200),
        'xml2.corriere.test/*' => Http::response(
            feedTitled('Corriere della Sera: News e Ultime notizie in tempo reale da Italia e Mondo'),
            200,
        ),
        '*' => Http::response(feedTitled('corriere.it'), 200),
    ]);
});

it('prints a pastable block covering every feed it verified', function (): void {
    Artisan::call('nova-rss:discover', ['url' => 'https://www.corriere.test/rss/']);
    $output = Artisan::output();

    expect($output)
        ->toContain("'categories' => [")
        ->toContain("'corriere' => [")
        ->toContain('https://xml2.corriere.test/feed-hp/homepage.xml')
        ->toContain('https://www.corriere.test/dynamic-feed/rss/section/Cronache.xml')
        ->toContain('https://www.corriere.test/dynamic-feed/rss/section/Spettacoli-Cinema.xml')
        // the page link that is not a feed must not appear
        ->not->toContain('index.shtml');
});

it('names feeds that share a generic title after their URL', function (): void {
    // Both section feeds report "corriere.it", so the path has to name them.
    Artisan::call('nova-rss:discover', ['url' => 'https://www.corriere.test/rss/']);

    expect(Artisan::output())
        ->toContain("'cronache' => ['title' => 'Cronache'")
        ->toContain("'spettacoli_cinema' => ['title' => 'Spettacoli Cinema'");
});

it('keeps a distinctive title but trims it at the separator', function (): void {
    Artisan::call('nova-rss:discover', ['url' => 'https://www.corriere.test/rss/']);

    expect(Artisan::output())->toContain("'corriere_della_sera' => ['title' => 'Corriere della Sera'");
});

it('reports what it did not check rather than dropping it silently', function (): void {
    Artisan::call('nova-rss:discover', ['url' => 'https://www.corriere.test/rss/', '--limit' => 1]);

    expect(Artisan::output())->toContain('further candidates were not checked');
});
