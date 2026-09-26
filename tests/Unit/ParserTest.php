<?php

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedParser;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\UnsupportedFeedFormat;
use Gabrielesbaiz\NovaCardRssNews\Feeds\ParserRegistry;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\AtomParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\JsonFeedParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\RdfParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\Rss2Parser;

dataset('parsers', [
    'rss2' => [Rss2Parser::class, 'sample-feed.xml', 'rss2'],
    'atom' => [AtomParser::class, 'sample-atom-feed.xml', 'atom'],
    'rdf' => [RdfParser::class, 'sample-rdf-feed.xml', 'rdf'],
    'json' => [JsonFeedParser::class, 'sample-json-feed.json', 'json'],
]);

it('parses every supported dialect into two items', function (string $class, string $file, string $key): void {
    /** @var FeedParser $parser */
    $parser = new $class;
    $body = fixture($file);

    expect($parser->key())->toBe($key)
        ->and($parser->supports($body))->toBeTrue();

    $parsed = $parser->parse($body);

    expect($parsed['items'])->toHaveCount(2)
        ->and($parsed['title'])->not->toBeNull()
        ->and($parsed['items'][0]->link)->toBe('https://example.test/news/1')
        ->and($parsed['items'][0]->publishedAt)->not->toBeNull()
        ->and($parsed['items'][1]->link)->toBe('https://example.test/news/2');
})->with('parsers');

it('decodes entities and strips markup from titles and summaries', function (): void {
    $parsed = (new Rss2Parser)->parse(fixture('sample-feed.xml'));
    $first = $parsed['items'][0];

    expect($first->title)->toContain("dell'auto")
        ->and($first->title)->not->toContain('&#039;')
        ->and($first->summary)->not->toContain('<img')
        ->and($first->summary)->not->toContain('<p>')
        ->and($first->summary)->toContain('&');
});

it('prefers rel=alternate links in atom entries', function (): void {
    $parsed = (new AtomParser)->parse(fixture('sample-atom-feed.xml'));

    expect($parsed['items'][1]->link)->toBe('https://example.test/news/2')
        ->and($parsed['items'][1]->summary)->toBe('Testo semplice.');
});

it('reads dublin core authors and dates from rdf', function (): void {
    $parsed = (new RdfParser)->parse(fixture('sample-rdf-feed.xml'));

    expect($parsed['items'][0]->author)->toBe('Mario Rossi')
        ->and($parsed['items'][0]->publishedAt?->toDateString())->toBe('2026-06-29');
});

it('extracts images, authors and categories from a rich rss feed', function (): void {
    $parsed = (new Rss2Parser)->parse(fixture('sample-rich-feed.xml'));

    expect($parsed['items'][0]->imageUrl)->toBe('https://rich.test/img/1.jpg')
        ->and($parsed['items'][0]->author)->toBe('Redazione')
        ->and($parsed['items'][0]->categories)->toBe(['Motori', 'Sicurezza'])
        ->and($parsed['items'][0]->summary)->toContain('Corpo completo')
        ->and($parsed['items'][1]->imageUrl)->toBe('https://rich.test/img/2.png')
        ->and($parsed['items'][2]->imageUrl)->toBe('https://rich.test/inline3.webp');
});

it('reads json feed authors and tags', function (): void {
    $parsed = (new JsonFeedParser)->parse(fixture('sample-json-feed.json'));

    expect($parsed['items'][0]->author)->toBe('Mario Rossi')
        ->and($parsed['items'][0]->categories)->toBe(['auto', 'vacanze'])
        ->and($parsed['items'][0]->imageUrl)->toBe('https://example.test/a.jpg')
        ->and($parsed['items'][0]->summary)->not->toContain('<strong>');
});

it('resolves the right parser for each payload and rejects junk', function (): void {
    $registry = app(ParserRegistry::class);

    expect($registry->resolve(fixture('sample-feed.xml'))->key())->toBe('rss2')
        ->and($registry->resolve(fixture('sample-atom-feed.xml'))->key())->toBe('atom')
        ->and($registry->resolve(fixture('sample-rdf-feed.xml'))->key())->toBe('rdf')
        ->and($registry->resolve(fixture('sample-json-feed.json'))->key())->toBe('json');

    $registry->resolve('not a feed at all', null, 'https://example.test');
})->throws(UnsupportedFeedFormat::class);

it('survives malformed xml without throwing', function (): void {
    expect((new Rss2Parser)->supports('<rss><channel><item></rss>'))->toBeFalse();
});
