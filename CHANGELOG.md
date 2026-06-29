# Changelog

All notable changes to `nova-card-rss-news` will be documented in this file.

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
