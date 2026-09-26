<p align="center">
    <img src="art/nova-card-rss-news-logo.png" alt="NovaCard RSS News" width="600">
</p>

# NovaCard RSS News

RSS on a Laravel Nova dashboard — four feed dialects normalized to one item shape, cached so a dead publisher never blanks a card, in three cards and four layouts.

[![Latest version](https://img.shields.io/packagist/v/gabrielesbaiz/nova-card-rss-news.svg?style=flat-square)](https://packagist.org/packages/gabrielesbaiz/nova-card-rss-news)
[![PHP](https://img.shields.io/packagist/dependency-v/gabrielesbaiz/nova-card-rss-news/php?style=flat-square)](composer.json)
[![Laravel](https://img.shields.io/packagist/dependency-v/gabrielesbaiz/nova-card-rss-news/illuminate%2Fsupport?style=flat-square&label=laravel)](composer.json)
[![Downloads](https://img.shields.io/packagist/dt/gabrielesbaiz/nova-card-rss-news.svg?style=flat-square)](https://packagist.org/packages/gabrielesbaiz/nova-card-rss-news)
[![Stars](https://img.shields.io/github/stars/gabrielesbaiz/nova-card-rss-news?style=flat-square&logo=github)](https://github.com/gabrielesbaiz/nova-card-rss-news/stargazers)
[![Sponsor](https://img.shields.io/github/sponsors/gabrielesbaiz?style=flat-square&label=sponsor&logo=github)](https://github.com/sponsors/gabrielesbaiz)

### 📖 [Read the documentation →](https://gabrielesbaiz.github.io/nova-card-rss-news/)

Every method and option, the five cache outcomes, the preset catalogue, and a
playground where all three cards run in your browser — switch layouts, search,
mark items read, and force a feed to fail.

> [!CAUTION]
> **Upgrading from 2.x?** Read [UPGRADING.md](UPGRADING.md) first. The Italian
> news and insurance catalogue is opt-in now, the `news` endpoint is `feed` with
> a different payload, and the card classes moved to `Cards\`. The old names
> still work; they go away in 4.0.

> [!IMPORTANT]
> A ⭐ costs you nothing and helps other developers find this package.
> [Sponsoring](https://github.com/sponsors/gabrielesbaiz) keeps it compatible
> with every new Laravel and Nova release.

## What it does

If the items already live in your database, Nova already does this: a card class
and a Vue file, about eighty lines, no dependency. Write that instead. This
package is for the case where the data is on **somebody else's server**, which is
where those eighty lines quietly become a project:

- **Four dialects** — RSS 2.0, Atom, RSS 1.0/RDF and JSON Feed — detected by content, because publishers get content types wrong. One normalized item out, entity-decoded and markup-stripped, sorted newest first and limited server side.
- **A cache that survives an outage.** Stale-while-revalidate with `ETag` revalidation, optional queued refresh, and a cached copy served — labelled as cached — when upstream is down.
- **Three cards, four layouts.** One feed, a source picker, or many feeds merged chronologically; hero, compact, grid or ticker, with search, thumbnails and read state.
- **A catalogue you control.** Opt-in presets, your own config, a `SourceProvider` contract, or a closure for per-user feeds.
- **OPML in and out**, plus feed discovery from a plain site URL.
- **Five artisan commands**, including a health check that exits non-zero in CI.

A refresh button that reaches a third party is a budget, not a button, so
cache-bypassing refreshes get their own much smaller rate limit. Ad-hoc feed
URLs are encrypted into the card's meta rather than accepted from the client —
the endpoints cannot be pointed at your internal network.

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- Laravel Nova 5, licensed separately

## Installation

```bash
composer require gabrielesbaiz/nova-card-rss-news

php artisan vendor:publish --tag=nova-card-rss-news-config
php artisan vendor:publish --tag=nova-card-rss-news-translations

php artisan nova-rss:check
```

The service provider is auto-discovered, and the card's JavaScript and CSS are
served by the package: no migrations, no tables, nothing to build. Both publish
steps are optional — a neutral international catalogue ships enabled, so the
card renders real news untouched.

**[Full installation guide →](https://gabrielesbaiz.github.io/nova-card-rss-news/#/install)**

## Artisan commands

| Command | Purpose |
|---|---|
| `nova-rss:check` | Fetch every source; report parser, item count and latency. Exits non-zero on failure. |
| `nova-rss:warm` | Pre-fetch every source so dashboards load from cache. |
| `nova-rss:discover {url}` | Find the feed behind a website URL. |
| `nova-rss:import {file}` | Turn an OPML export into a config block. |
| `nova-rss:export` | Write the resolved catalogue back out as OPML. |

See the [commands page](https://gabrielesbaiz.github.io/nova-card-rss-news/#/commands).

## Documentation

| | |
|---|---|
| [Documentation site](https://gabrielesbaiz.github.io/nova-card-rss-news/) | Everything: install, configure, operate. |
| [Playground](https://gabrielesbaiz.github.io/nova-card-rss-news/#/play) | All three cards running in your browser, every option switchable. |
| [Cards & layouts](https://gabrielesbaiz.github.io/nova-card-rss-news/#/cards) | The three classes and what each option does. |
| [Sources](https://gabrielesbaiz.github.io/nova-card-rss-news/#/sources) | Presets, your own feeds, providers, per-user catalogues. |
| [API reference](https://gabrielesbaiz.github.io/nova-card-rss-news/#/api) | Every method, the services, events and endpoints. |
| [UPGRADING.md](UPGRADING.md) | Upgrading from 2.x. Read before you start. |
| [CHANGELOG.md](CHANGELOG.md) | What changed, and when. |

## Testing

```bash
composer test        # Pest — 89 tests, no network access
composer analyse     # PHPStan level 5, with Larastan
composer format      # Pint
```

Feed dialects run against fixtures; the HTTP client is faked. The suite covers
the cache pipeline, SSRF rejection of unsigned URLs, both rate limits, every
command and an OPML round trip. The documentation playground has its own checks
in `tests/Browser/`, and CI runs all of it across PHP 8.2–8.4 and Laravel 11–12.

## Contributing

Pull requests are welcome, and a failing test is the fastest way to a fix. The
house rules — no network in tests, new feeds go in a preset, built assets are
committed — are on the
[project page](https://gabrielesbaiz.github.io/nova-card-rss-news/#/project).

## Security vulnerabilities

Email **gabriele@sbaiz.com** rather than opening a public issue. The threat model
and what is deliberate are on the
[security page](https://gabrielesbaiz.github.io/nova-card-rss-news/#/security).

## Credits

Written and maintained by [Gabriele Sbaiz](https://github.com/gabrielesbaiz),
with thanks to [everyone who has sent a pull request](../../contributors).

Built on [Laravel Nova](https://nova.laravel.com),
[spatie/laravel-package-tools](https://github.com/spatie/laravel-package-tools)
and PHP's own `SimpleXML` — no XML library is added to your application.

## Support this package

If it is useful to you:

- ⭐ **Star the repo.** Free, thirty seconds, and it is the first signal other developers look at.
- ❤️ **[Become a sponsor](https://github.com/sponsors/gabrielesbaiz).** From $5 a month.
- 🐛 **Open a good issue.** The feed URL and what you expected is worth more than you think.
- 🗣️ **Tell another Laravel developer.** Word of mouth is how packages survive.

[![Sponsor on GitHub](https://img.shields.io/badge/Sponsor-gabrielesbaiz-ff69b4?style=for-the-badge&logo=github-sponsors)](https://github.com/sponsors/gabrielesbaiz)

## Disclaimer

This package is provided **as is**, without warranty of any kind, express or
implied, including but not limited to the warranties of merchantability, fitness
for a particular purpose, title and non-infringement. To the fullest extent
permitted by applicable law, in no event shall the authors, copyright holders or
contributors be liable for any claim, damages or other liability — whether in an
action of contract, tort or otherwise — arising from, out of or in connection
with this package or its use.

It renders content published by third parties, whose availability, accuracy and
continued existence are outside its control. The bundled presets are a
convenience, not an endorsement; feeds break, move and disappear without notice.
Whoever deploys it is responsible for deciding what is acceptable to display
inside an administration panel, and for reviewing the code before putting it in
front of one. Nothing here constitutes security, legal or compliance advice.

Use of this package is entirely at your own risk.

## License

MIT. See [LICENSE.md](LICENSE.md). The MIT licence's warranty disclaimer and
limitation of liability apply in full, alongside the disclaimer above.
