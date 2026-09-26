<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Tests;

use Gabrielesbaiz\NovaCardRssNews\NovaCardRssNewsServiceProvider;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Foundation\Application;
use Illuminate\Routing\RouteCollection;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            NovaCardRssNewsServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    public function getEnvironmentSetUp($app): void
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('database.default', 'testing');
        config()->set('cache.default', 'array');

        // Nova itself is not booted in the package test app, so its middleware
        // group does not exist; the routes are exercised without it.
        config()->set('nova-card-rss-news.routes.middleware', []);
    }

    /**
     * Re-register the package routes after changing route configuration
     * (middleware and throttles are read when the routes are declared).
     */
    protected function refreshRoutes(): void
    {
        $this->app['router']->setRoutes(new RouteCollection);

        (new NovaCardRssNewsServiceProvider($this->app))->registerRoutes();
    }

    /**
     * Replace the catalogue with a deterministic, offline set of sources.
     *
     * @param  array<string, array<string, mixed>>  $sources
     */
    protected function useSources(array $sources, string $category = 'demo'): void
    {
        config()->set('nova-card-rss-news.sources', [[
            'categories' => [
                $category => ['label' => 'Demo', 'sources' => $sources],
            ],
        ]]);

        $this->app->forgetInstance(SourceRepository::class);
    }
}
