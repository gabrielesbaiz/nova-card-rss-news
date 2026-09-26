# Upgrading

## 2.x → 3.0

3.0 is a rewrite. The cards look and behave the same, but everything behind them
moved. Budget fifteen minutes for a typical installation.

### 1. Requirements

| | 2.x | 3.0 |
|---|---|---|
| PHP | 8.0+ | **8.2+** |
| Laravel | 10, 11, 12 | **11, 12** |
| Nova | 5 | 5 |

### 2. The Italian catalogue is opt-in

This is the change most installations will notice first. 2.x shipped an Italian
news, insurance, motoring, travel and sport catalogue enabled by default. It is
all still here, reclassified into presets, and none of the source keys changed.

Republish the config (or add the `sources` key to your existing one) and enable
what you were using:

```php
// config/nova-card-rss-news.php
use Gabrielesbaiz\NovaCardRssNews\Presets;

'sources' => [
    Presets\StarterPreset::class,        // neutral international default

    Presets\ItNewsPreset::class,         // quotidiani + agenzie_stampa
    Presets\ItInsurancePreset::class,    // the three assicurazioni_* categories
    Presets\ItMotoriPreset::class,       // motori
    Presets\ItEconomyPreset::class,      // economia
    Presets\ItTravelPreset::class,       // viaggi
    Presets\ItSportPreset::class,        // sport
    Presets\AggregatorsPreset::class,    // aggregatori
],
```

`->source('motor1')`, `->source('ivass')` and every other key keep working once
the preset that owns them is enabled.

**Already customised your published config?** Do not rewrite it. Paste the whole
`categories` array into `sources` as a single inline entry — the v2 shape is
still understood:

```php
'sources' => [
    Presets\StarterPreset::class,

    [
        'categories' => [
            // …your existing v2 array, unchanged…
        ],
    ],
],
```

Confirm nothing is missing:

```bash
php artisan nova-rss:export | grep xmlUrl | wc -l
php artisan nova-rss:check
```

### 3. Card classes moved

| 2.x | 3.0 |
|---|---|
| `Gabrielesbaiz\NovaCardRssNews\NovaCardRssNews` | `Gabrielesbaiz\NovaCardRssNews\Cards\RssNewsCard` |
| `Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsSelect` | `Gabrielesbaiz\NovaCardRssNews\Cards\RssNewsSelectCard` |
| `Gabrielesbaiz\NovaCardRssNews\CardServiceProvider` | `Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsServiceProvider` |

The old names still exist as `@deprecated` subclasses, so your dashboards keep
working untouched. They are removed in 4.0. A find-and-replace is the whole
migration:

```bash
grep -rl 'NovaCardRssNews\\NovaCardRssNews' app/ \
  | xargs sed -i '' \
      -e 's/NovaCardRssNews\\NovaCardRssNewsSelect/NovaCardRssNews\\Cards\\RssNewsSelectCard/g' \
      -e 's/NovaCardRssNews\\NovaCardRssNews/NovaCardRssNews\\Cards\\RssNewsCard/g'
```

Then update the class names in the card expressions themselves
(`new NovaCardRssNews` → `RssNewsCard::make()`).

If you registered the provider manually in `config/app.php`, point it at
`NovaCardRssNewsServiceProvider`.

### 4. `RssFeedService` is gone

The all-static service was removed with no alias. Its replacement is injectable,
cached and covered by tests:

```php
// 2.x
$data = RssFeedService::getRssFeed('motor1');
$data['title'];
$data['feed'];        // array of ['title', 'link', 'description', 'pubDate']

// 3.0
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;

$feed = app(FeedManager::class)->get(
    app(SourceRepository::class)->findOrFail('motor1'),
);

$feed->title;
$feed->items;         // array of FeedItem objects
$feed->items[0]->publishedAt;   // CarbonImmutable
```

### 5. Endpoints and payload

| 2.x | 3.0 |
|---|---|
| `GET nova-vendor/nova-card-rss-news/news?source_key=` | `GET …/feed?source=` |
| — | `GET …/stream?sources[]=&categories[]=` |
| `GET …/sources` | `GET …/sources` (unchanged path, richer payload) |

The item shape changed:

| 2.x | 3.0 |
|---|---|
| `feed` | `items` |
| `description` | `summary` |
| `pubDate` (raw string, unsorted) | `published_at` (ISO-8601, sorted newest first) |
| — | `id`, `image_url`, `author`, `categories`, `source_key`, `source_title`, `source_site` |
| — | `stale`, `parser`, `fetched_at` on the envelope |

`limit` is now applied server side, so the response contains exactly what the
card renders.

This only matters if you called the endpoints yourself; the bundled Vue was
rewritten with them.

### 6. Config keys

`categories` at the top level is replaced by `sources` (see step 2). Everything
else is new and optional — `http`, `cache`, `queue`, `routes`, `ui`, `defaults`.
Existing published configs keep working once `sources` is added, because unknown
top-level keys are ignored.

The most likely thing you now want to set:

```php
'ui' => ['favicons' => 'google'],   // 2.x always used Google's favicon service
```

3.0 defaults to `none` — initials instead of favicons — because the Google S2
service is told which feeds your dashboard reads. Set it back to `google` if you
prefer the icons.

### 7. Build tooling (contributors only)

Laravel Mix was replaced by Vite. `webpack.mix.js` and `nova.mix.js` are gone,
`npm run dev` / `npm run build` do the same jobs, and the output paths
(`dist/js/card.js`, `dist/css/card.css`) are unchanged. Host applications are
unaffected — the assets ship built.

### After upgrading

```bash
php artisan optimize:clear
php artisan nova-rss:check
```

If a feed that worked in 2.x now fails, run `nova-rss:discover <site-url>`: a
few publishers moved their feeds between these releases, and 3.0 tells you
honestly instead of rendering an empty card.
