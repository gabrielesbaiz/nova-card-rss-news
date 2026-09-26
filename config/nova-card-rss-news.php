<?php

use Gabrielesbaiz\NovaCardRssNews\Presets;

return [

    /*
    |--------------------------------------------------------------------------
    | Feed sources
    |--------------------------------------------------------------------------
    |
    | Every entry contributes sources to the catalogue, merged in order — later
    | entries override earlier ones by source key. An entry may be:
    |
    |   - a preset class shipped with the package (see Presets/)
    |   - any class implementing Contracts\SourceProvider
    |   - a closure returning sources (per-user or database driven)
    |   - an inline ['categories' => [...]] array (the v2 config shape)
    |
    | Uncomment the presets you want. `nova-rss:check` validates them all.
    |
    */

    'sources' => [

        Presets\StarterPreset::class,

        // Presets\ItNewsPreset::class,
        // Presets\ItInsurancePreset::class,
        // Presets\ItMotoriPreset::class,
        // Presets\ItEconomyPreset::class,
        // Presets\ItTravelPreset::class,
        // Presets\ItSportPreset::class,
        // Presets\AggregatorsPreset::class,

        // Your own feeds, in the same shape the presets use:
        // [
        //     'categories' => [
        //         'team' => [
        //             'label' => 'Team',
        //             'sources' => [
        //                 'company_blog' => ['title' => 'Company blog', 'url' => 'https://example.com/feed'],
        //             ],
        //         ],
        //     ],
        // ],

        // Per-user feeds straight from your own model:
        // fn () => auth()->user()?->rssFeeds->map->toRssSource() ?? [],

    ],

    /*
    |--------------------------------------------------------------------------
    | HTTP client
    |--------------------------------------------------------------------------
    */

    'http' => [
        'timeout' => 8,
        'connect_timeout' => 3,
        'retries' => 2,
        'retry_delay' => 200,
        'max_redirects' => 5,
        'verify' => true,
        'user_agent' => 'Mozilla/5.0 (compatible; NovaCardRssNews/3.0; +https://github.com/gabrielesbaiz/nova-card-rss-news)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache
    |--------------------------------------------------------------------------
    |
    | `ttl` is how long a feed is considered fresh. Past it, the cached copy is
    | still served for `stale_ttl` more seconds while a refresh runs — in the
    | background when the queue is enabled, inline otherwise.
    |
    */

    'cache' => [
        'store' => null,
        'ttl' => 300,
        'stale_ttl' => 3600,
        'prefix' => 'nova-card-rss-news',
    ],

    'queue' => [
        'enabled' => false,
        'connection' => null,
        'queue' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | `authorize` may be a gate ability name or a closure; null allows every
    | Nova user. Throttles are "attempts,minutes" strings.
    |
    */

    'routes' => [
        'middleware' => ['nova'],
        'authorize' => null,
        'throttle' => '120,1',
        'fresh_throttle' => '20,1',
    ],

    /*
    |--------------------------------------------------------------------------
    | Interface
    |--------------------------------------------------------------------------
    |
    | favicons: 'google' resolves site icons through Google's S2 service, which
    | tells Google which feeds this dashboard reads. Set 'none' to draw initials
    | locally instead and make no third-party request.
    |
    */

    'ui' => [
        'favicons' => 'google',
        'images' => true,
    ],

    'defaults' => [
        'limit' => 10,
        'layout' => 'hero',
        'auto_refresh' => 0,
        'search' => false,
        'read_state' => true,
    ],

];
