<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews;

use Gabrielesbaiz\NovaCardRssNews\Console\CheckFeedsCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\DiscoverFeedCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\ExportOpmlCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\ImportOpmlCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\WarmFeedsCommand;
use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Feeds\HttpFeedFetcher;
use Gabrielesbaiz\NovaCardRssNews\Feeds\ParserRegistry;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\AtomParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\JsonFeedParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\RdfParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers\Rss2Parser;
use Gabrielesbaiz\NovaCardRssNews\Sources\FeedDiscoverer;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NovaCardRssNewsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('nova-card-rss-news')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasCommands([
                CheckFeedsCommand::class,
                DiscoverFeedCommand::class,
                ExportOpmlCommand::class,
                ImportOpmlCommand::class,
                WarmFeedsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(ParserRegistry::class, static fn (): ParserRegistry => new ParserRegistry([
            new Rss2Parser,
            new AtomParser,
            new RdfParser,
            new JsonFeedParser,
        ]));

        $this->app->singleton(FeedFetcher::class, fn ($app): FeedFetcher => new HttpFeedFetcher(
            $app->make(HttpFactory::class),
            (array) config('nova-card-rss-news.http', []),
        ));

        $this->app->singleton(SourceRepository::class, fn ($app): SourceRepository => new SourceRepository(
            $app,
            (array) config('nova-card-rss-news.sources', []),
        ));

        $this->app->singleton(FeedManager::class, fn ($app): FeedManager => new FeedManager(
            $app->make(FeedFetcher::class),
            $app->make(ParserRegistry::class),
            $app->make('cache'),
            $app->make('events'),
            (array) config('nova-card-rss-news', []),
        ));

        $this->app->singleton(FeedDiscoverer::class, fn ($app): FeedDiscoverer => new FeedDiscoverer(
            $app->make(FeedFetcher::class),
            $app->make(ParserRegistry::class),
        ));
    }

    public function packageBooted(): void
    {
        $this->app->booted(function (): void {
            $this->registerRoutes();
        });

        if (class_exists(Nova::class)) {
            Nova::serving(function (ServingNova $event): void {
                Nova::script('nova-card-rss-news', __DIR__.'/../dist/js/card.js');
                Nova::style('nova-card-rss-news', __DIR__.'/../dist/css/card.css');
                // Fall back to English when the app locale has no bundled file.
                $locale = __DIR__.'/../resources/lang/'.app()->getLocale().'.json';

                Nova::translations(is_file($locale) ? $locale : __DIR__.'/../resources/lang/en.json');
            });
        }
    }

    public function registerRoutes(): void
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        $middleware = (array) config('nova-card-rss-news.routes.middleware', ['nova']);

        if (config('nova-card-rss-news.routes.authorize') !== null) {
            $middleware[] = Http\Middleware\AuthorizeRssCard::class;
        }

        Route::middleware($middleware)
            ->prefix('nova-vendor/nova-card-rss-news')
            ->group(__DIR__.'/../routes/api.php');
    }
}
