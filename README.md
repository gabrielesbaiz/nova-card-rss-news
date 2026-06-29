# Nova Card RSS News

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gabrielesbaiz/nova-card-rss-news.svg?style=flat-square)](https://packagist.org/packages/gabrielesbaiz/nova-card-rss-news)
[![Total Downloads](https://img.shields.io/packagist/dt/gabrielesbaiz/nova-card-rss-news.svg?style=flat-square)](https://packagist.org/packages/gabrielesbaiz/nova-card-rss-news)
[![License](https://img.shields.io/packagist/l/gabrielesbaiz/nova-card-rss-news.svg?style=flat-square)](LICENSE.md)

A polished Laravel Nova dashboard card that streams RSS feeds with a hero item, branded gradient background, source favicon, relative timestamps and skeleton loader. Ships with a category-grouped catalogue of curated Italian news and insurance sources, fully configurable from your app.

Two card variants:

- **`NovaCardRssNews`** — one card, one source. Pinned to the source you choose in PHP.
- **`NovaCardRssNewsSelect`** — same look, with an in-header dropdown to switch source at runtime. Selection persists per browser in `localStorage`.

## Features

- Brand-aware UI driven by Nova's `--colors-primary-500` CSS variable — gradient background, pulsing live dot, glowing hover state. Automatically picks up your Nova primary colour.
- Source favicon resolved automatically via Google's S2 service (no manual logo upload).
- Hero card + compact list layout with hover-translate chevron, 2-line title clamp and shine-on-hover effect.
- Relative timestamps in Italian (`5 min fa`, `2 h fa`, `3 g fa`) with the full date in a tooltip; updates every 30 seconds.
- Skeleton shimmer while loading, friendly empty state, manual refresh button, custom thin scrollbar.
- HTML entities (`&#039;`, `&amp;`, …) and stray `<img>` / `<p>` tags are stripped server-side before render.
- 5-minute response cache on the news endpoint to avoid hammering upstream feeds.
- Sources organised by category — comment out anything you don't want exposed.

## Requirements

- PHP 8.0+
- Laravel 10, 11 or 12
- Laravel Nova 5

## Installation

```bash
composer require gabrielesbaiz/nova-card-rss-news
```

Publish the config file (optional — the bundled catalogue works out of the box):

```bash
php artisan vendor:publish --tag=nova-card-rss-news-config
```

The card's JS and CSS are auto-served by the package's `CardServiceProvider`. Nothing to publish or build on the host app.

## Usage

Add the card to any dashboard or resource:

```php
use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNews;
use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsSelect;

public function cards(): array
{
    return [
        // Fixed-source card
        (new NovaCardRssNews)
            ->source('ansa_motori')
            ->limit(5)
            ->width('1/3'),

        // User-switchable source card
        (new NovaCardRssNewsSelect)
            ->defaultSource('motor1')
            ->limit(10)
            ->width('1/3'),
    ];
}
```

### `NovaCardRssNews`

| Method                  | Description                                              | Default   |
| ----------------------- | -------------------------------------------------------- | --------- |
| `->source(string $key)` | RSS source key from the config catalogue.                | `motor1`  |
| `->limit(int $limit)`   | Maximum number of news items to render.                  | `10`      |
| `->width(string $w)`    | Standard Nova card width (`1/3`, `1/2`, `full`).         | `1/2`     |

### `NovaCardRssNewsSelect`

| Method                          | Description                                                      | Default   |
| ------------------------------- | ---------------------------------------------------------------- | --------- |
| `->defaultSource(string $key)`  | Initial source shown when the user has no persisted choice.      | `motor1`  |
| `->limit(int $limit)`           | Maximum number of news items to render.                          | `10`      |
| `->width(string $w)`            | Standard Nova card width.                                        | `1/2`     |

The dropdown is populated from the `GET /nova-vendor/nova-card-rss-news/sources` endpoint and groups options by category via `<optgroup>`. Each browser remembers the last picked source per card.

## Configuration

The published config lives at `config/nova-card-rss-news.php`. Sources are grouped by category. To disable a feed, comment its line out — the rest of the catalogue keeps working.

```php
return [

    'categories' => [

        'assicurazioni_specializzate' => [
            'label' => 'Assicurazioni — Testate specializzate',
            'sources' => [
                'assinews' => [
                    'title' => 'Assinews',
                    'url' => 'https://www.assinews.it/feed/',
                ],
                // 'insurance_trade' => [ ... ],  // ← disabled
                'insurance_up' => [
                    'title' => 'InsuranceUp',
                    'url' => 'https://www.insuranceup.it/feed/',
                ],
            ],
        ],

        'motori' => [
            'label' => 'Motori',
            'sources' => [
                'motor1' => [
                    'title' => 'Motor1.com',
                    'url' => 'https://it.motor1.com/rss/articles/all/',
                ],
            ],
        ],

    ],

];
```

### Adding a new source

1. Pick the category that fits (or add a new key under `'categories'` with `label` + `sources`).
2. Add a new entry to `sources` keyed by a stable slug — that slug becomes the value you pass to `->source('...')` / `->defaultSource('...')`.
3. Reference it from a card.

```php
'sources' => [
    'my_custom_feed' => [
        'title' => 'My Custom Feed',
        'url' => 'https://example.com/feed.xml',
    ],
],
```

### Bundled catalogue

| Category                                                  | Sources |
| --------------------------------------------------------- | ------- |
| **Assicurazioni — Testate specializzate**                 | Assinews, Intermedia Channel, InsuranceUp |
| **Assicurazioni — Autorità e istituzioni**                | IVASS, ANIA |
| **Assicurazioni — Sezioni da testate economiche**         | Il Sole 24 ORE — Finanza, FIRSTonline |
| **Motori**                                                | Motor1, Alvolante, ANSA Motori, Corriere Motori, Il Sole 24 ORE Motori, Motori.it |
| **Economia / Finanza**                                    | Il Sole 24 ORE (Italia, Finanza, Norme & Tributi, Risparmio) |
| **Quotidiani nazionali**                                  | Repubblica (home/politica/economia/cronaca), Corriere homepage, La Stampa, Il Fatto Quotidiano, Panorama |
| **Agenzie di stampa**                                     | ANSA (tutte/top news/cronaca/sport/economia/cultura) |
| **Sport**                                                 | Gazzetta dello Sport |
| **Aggregatori**                                           | Google News Italia |

## How it works

```
┌────────────────────────────┐    GET /sources       ┌────────────────────────┐
│  CardSelect.vue (browser)  │ ────────────────────► │   SourcesController    │
└────────────────────────────┘                       └──────────┬─────────────┘
                                                                │
                                                                ▼
                                              config('nova-card-rss-news.categories')

┌────────────────────────────┐    GET /news?key      ┌────────────────────────┐    fetch RSS
│      Card.vue (browser)    │ ────────────────────► │     NewsController     │ ─────────────►  Upstream feed
└────────────────────────────┘                       └──────────┬─────────────┘                 (cached 5 min)
                                                                │
                                                                ▼
                                                       RssFeedService::getRssFeed()
                                                       — entity-decode title/desc
                                                       — strip <img>, <p>
                                                       — return title, url, items[]
```

## Testing

```bash
composer test
```

The package ships a Pest 3 suite covering: config shape, source lookup, HTML entity decoding, the `/sources` controller payload, the `NewsController` 404 path, both card classes' meta payloads, and an arch test that bans `dd`/`dump`/`ray` from the source.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release notes.

## Credits

- [Gabriele Sbaiz](https://github.com/gabrielesbaiz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). See [LICENSE.md](LICENSE.md).
