<?php

namespace Gabrielesbaiz\NovaCardRssNews;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CardServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        $this->publishes([
            __DIR__ . '/../config/nova-card-rss-news.php' => config_path('nova-card-rss-news.php'),
        ], 'nova-card-rss-news-config');

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-card-rss-news', __DIR__ . '/../dist/js/card.js');
            Nova::style('nova-card-rss-news', __DIR__ . '/../dist/css/card.css');
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/nova-card-rss-news.php',
            'nova-card-rss-news'
        );
    }

    /**
     * Register the card's routes.
     *
     * @return void
     */
    protected function routes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova'])
            ->prefix('nova-vendor/nova-card-rss-news')
            ->group(__DIR__ . '/../routes/api.php');
    }
}
