<?php

use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedException;
use Gabrielesbaiz\NovaCardRssNews\Sources\OpmlReader;
use Gabrielesbaiz\NovaCardRssNews\Sources\OpmlWriter;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;

const OPML = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<opml version="2.0">
  <head><title>My feeds</title></head>
  <body>
    <outline text="Tech News">
      <outline type="rss" text="The Verge" xmlUrl="https://www.theverge.com/rss/index.xml" htmlUrl="https://www.theverge.com" />
      <outline type="rss" text="Ars Technica" xmlUrl="https://feeds.arstechnica.com/arstechnica/index" />
    </outline>
    <outline type="rss" text="Laravel News" xmlUrl="https://feed.laravel-news.com/" />
  </body>
</opml>
XML;

it('reads nested outlines as categories and leaves as sources', function (): void {
    $sources = (new OpmlReader)->read(OPML);

    expect($sources)->toHaveCount(3)
        ->and($sources[0]->key)->toBe('the_verge')
        ->and($sources[0]->categoryKey)->toBe('tech_news')
        ->and($sources[0]->categoryLabel())->toBe('Tech News')
        ->and($sources[0]->siteUrl)->toBe('https://www.theverge.com')
        ->and($sources[2]->categoryKey)->toBe('general');
});

it('rejects a document that is not OPML', function (): void {
    (new OpmlReader)->read('<html><body>nope</body></html>');
})->throws(FeedException::class);

it('round-trips the catalogue through OPML without losing feeds', function (): void {
    $original = (new OpmlReader)->read(OPML);
    $opml = (new OpmlWriter)->write($original);
    $reimported = (new OpmlReader)->read($opml);

    expect($reimported->pluck('url')->sort()->values()->all())
        ->toBe($original->pluck('url')->sort()->values()->all())
        ->and($reimported->pluck('key')->sort()->values()->all())
        ->toBe($original->pluck('key')->sort()->values()->all());
});

it('exports the resolved catalogue as OPML', function (): void {
    $opml = (new OpmlWriter)->write(app(SourceRepository::class)->all()->values());

    expect($opml)->toContain('<opml version="2.0">')
        ->toContain('xmlUrl="https://feed.laravel-news.com/"')
        ->toContain('text="Development"');
});

it('escapes special characters when writing', function (): void {
    $sources = (new OpmlReader)->read(str_replace('The Verge', 'A &amp; B &lt;news&gt;', OPML));
    $opml = (new OpmlWriter)->write($sources);

    expect($opml)->toContain('&amp;')
        ->and((new OpmlReader)->read($opml)->first()->label())->toBe('A & B <news>');
});
