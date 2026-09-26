# Changelog

All notable changes to `nova-card-rss-news` will be documented in this file.

## 3.0.0 - 2026-09-25

A rewrite. The cards look the same; everything behind them is new.

**Breaking**

- The Italian news / insurance catalogue is no longer enabled by default. It moved into opt-in presets (`ItNewsPreset`, `ItInsurancePreset`, `ItMotoriPreset`, `ItEconomyPreset`, `ItTravelPreset`, `ItSportPreset`, `AggregatorsPreset`); no source key changed. A neutral international `StarterPreset` ships enabled instead.
- `config('nova-card-rss-news.categories')` is replaced by `sources`, a list of providers. The v2 `categories` array is still accepted as an inline entry.
- The `news` endpoint is now `feed`, with a normalized payload (`items`, `summary`, ISO-8601 `published_at`, `id`, `image_url`, `author`, `categories`, `source_*`) and the limit applied server side.
- Cards moved to `Cards\RssNewsCard` / `Cards\RssNewsSelectCard`; `CardServiceProvider` is now `NovaCardRssNewsServiceProvider`. Old names remain as deprecated aliases until 4.0.
- `RssFeedService` was removed in favour of the injectable `FeedManager`.
- PHP 8.2+ and Laravel 11+ are required.
- Favicons default to `none`; set `ui.favicons` to `google` for the 2.x behaviour.

See [UPGRADING.md](UPGRADING.md).

**Added**

- `RssNewsStreamCard`: several feeds merged into one chronological stream, badged by source, deduplicated, resilient to one feed failing.
- Four layouts — `hero`, `compact`, `grid`, `ticker` — plus client-side search with highlighting, feed thumbnails, read/unread dimming with a "new since last visit" badge, bookmarks, and auto refresh that pauses on hidden tabs and on hover.
- RSS 1.0 / RDF and JSON Feed support, alongside RSS 2.0 and Atom. Detection is by content. `content:encoded`, `dc:creator`, `dc:date`, `media:content`, `media:thumbnail` and image enclosures are read.
- A pluggable source layer: presets, any `SourceProvider` class, closures (per-user feeds) and inline arrays, merged by `SourceRepository`.
- `nova-rss:check`, `nova-rss:warm`, `nova-rss:import` (OPML), `nova-rss:export` (OPML) and `nova-rss:discover` (find a feed from a site URL).
- `FeedFetched` and `FeedFetchFailed` events.
- Ad-hoc feeds via `->feed($url, $title)`, with the URL encrypted into the card meta so the endpoint can never be pointed at an arbitrary host.
- Optional gate on the endpoints, a separate and much tighter rate limit for cache-bypassing refreshes, and configurable route middleware.
- English and Italian translations; relative dates via `Intl.RelativeTimeFormat` in the viewer's locale.
- `aria-live` updates, focus-visible outlines and `prefers-reduced-motion` support.

**Fixed**

- The refresh button now actually refreshes. In 2.x it only busted the browser cache while the server kept serving its 5-minute cached copy.
- Feed requests have timeouts, retries, a real user agent, gzip and bounded redirects (`file_get_contents` had none of these, and a hung publisher hung the dashboard).
- Fast source switching can no longer be overwritten by a slower earlier response; in-flight requests are aborted.
- Every card on a page shares one 30-second clock instead of running its own interval.

**Changed**

- Caching is stale-while-revalidate with conditional `ETag` / `Last-Modified` revalidation and optional queued background refresh. A failed fetch serves the cached copy, flagged as stale, instead of blanking the card.
- The Vue is `<script setup>` with composables and shared parts; Laravel Mix was replaced by Vite. Output paths are unchanged.
- Test suite grown to 89 tests; PHPStan level 5 and CI across PHP 8.2–8.4 and Laravel 11–12.

## 2.4.0 - 2026-06-30

- Support Atom feeds (`<feed>`/`<entry>`) in addition to RSS 2.0. `RssFeedService` now detects the feed type and parses Atom entries — including `href`-attribute links (preferring `rel="alternate"`), `<summary>`/`<content>` descriptions, and `<published>`/`<updated>` dates. Fixes Quattroruote (`.../newsRss/feed.xml`) and other Atom sources previously showing "Nessuna notizia disponibile".

## 2.3.2 - 2026-06-29

- Fix `NovaCardRssNewsSelect` localStorage collision: multiple cards on the same dashboard now persist their source selection independently. The storage key now includes the card's `defaultSource()` value, not just the component name.

## 2.3.1 - 2026-06-29

- Update IVASS feed URL to `https://www.ivass.it/util/index.rss.html?lingua=it`.
- Remove dead / 404 / non-RSS sources after end-to-end validation: Insurance Trade, Facile.it, Segugio.it, Assicurazione.it, Corriere Politica, Corriere Sport, Il Post, Adnkronos, Repubblica Sport.

## 2.3.0 - 2026-06-29

- Add 4 insurance-focused categories: `assicurazioni_specializzate` (Assinews, Intermedia Channel, Insurance Trade, InsuranceUp), `assicurazioni_istituzioni` (IVASS, ANIA), `assicurazioni_comparatori` (Facile.it, Segugio.it, Assicurazione.it), `assicurazioni_economiche` (Il Sole 24 ORE — Finanza, FIRSTonline Assicurazioni).
- Reorder categories: insurance first, then Motori, Economia, Quotidiani, Agenzie di stampa, Sport, Aggregatori.

## 2.2.0 - 2026-06-29

**Breaking:** sources moved from internal `src/Data/rss_sources.json` to a publishable Laravel config.

- New `config/nova-card-rss-news.php` config file. Sources are now grouped by category (Motori, Agenzie di stampa, Quotidiani nazionali, Economia / Finanza, Sport, Aggregatori). Disable any source by commenting it out.
- Greatly expanded catalogue: ANSA (Top News, Cronaca, Sport, Economia, Cultura), Adnkronos, Repubblica, Corriere, La Stampa, Il Fatto Quotidiano, Il Post, Panorama, Il Sole 24 ORE (Italia, Finanza, Norme & Tributi, Risparmio), Gazzetta dello Sport, Google News Italia.
- `GET /sources` endpoint now returns sources grouped by category.
- `NovaCardRssNewsSelect` dropdown now renders sources inside `<optgroup>` elements by category.
- To customise the catalogue in the host app, run `php artisan vendor:publish --tag=nova-card-rss-news-config`.

## 2.1.0 - 2026-06-29

- New `NovaCardRssNewsSelect` card variant with an in-header dropdown to switch RSS source at runtime. Selection persists per browser via `localStorage`.
- New `GET /nova-vendor/nova-card-rss-news/sources` endpoint exposing the list of configured sources.
- `RssFeedService` now decodes HTML entities (e.g. `&#039;`, `&amp;`) in titles and descriptions.

## 2.0.0 - 2026-06-29

Full UI/UX redesign of the card.

- Branded radial-gradient background driven by Nova's `--colors-primary-500` CSS variable (auto-adapts to host app's primary colour).
- Header strip: source favicon (Google S2), feed title, pulsing live indicator, manual refresh button.
- First news item rendered as a "hero" with badge, shine-on-hover sweep, lift + glow on hover.
- Remaining items in a compact list with hover-translate chevron, primary-tinted hover background, and 2-line title clamp.
- Italian relative time labels ("5 min fa", "2 h fa", "3 g fa") with absolute date in tooltip; updates every 30s.
- Skeleton shimmer loader while fetching; empty-state icon + message.
- Stagger fade-in animation on items.
- Custom thin primary-tinted scrollbar.
