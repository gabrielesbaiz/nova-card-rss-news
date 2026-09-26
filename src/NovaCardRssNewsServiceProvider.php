<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews;

use Gabrielesbaiz\NovaCardRssNews\Console\CheckFeedsCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\DiscoverFeedCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\ExportOpmlCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\ImportOpmlCommand;
use Gabrielesbaiz\NovaCardRssNews\Console\ListFeedsCommand;
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
                ListFeedsCommand::class,
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
                $this->registerAssets();
                $this->registerTranslations();
            });
        }
    }

    /**
     * Nova serves package assets by name, with no fingerprint, so a rebuilt
     * file keeps its old URL and a browser holds on to the stale bundle. The
     * name carries a short hash of the built files, making every build a new
     * URL.
     */
    protected function registerAssets(): void
    {
        $js = __DIR__.'/../dist/js/card.js';
        $css = __DIR__.'/../dist/css/card.css';

        $build = substr(md5((string) @md5_file($js).(string) @md5_file($css)), 0, 8);

        Nova::script("nova-card-rss-news-{$build}", $js);
        Nova::style("nova-card-rss-news-{$build}", $css);
    }

    /**
     * English first as a base, then the app locale on top, so a locale the
     * package does not ship degrades to English rather than to raw keys.
     */
    protected function registerTranslations(): void
    {
        $directory = __DIR__.'/../resources/lang/';

        Nova::translations($directory.'en.json');

        $locale = $directory.app()->getLocale().'.json';

        if (is_file($locale)) {
            Nova::translations($locale);
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
