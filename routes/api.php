<?php

use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\FeedController;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\SourcesController;
use Gabrielesbaiz\NovaCardRssNews\Http\Controllers\StreamController;
use Gabrielesbaiz\NovaCardRssNews\Http\Middleware\ThrottleFreshFeed;
use Illuminate\Support\Facades\Route;

$throttle = config('nova-card-rss-news.routes.throttle', '120,1');

Route::middleware(["throttle:{$throttle}"])->group(function (): void {
    Route::get('sources', [SourcesController::class, 'index']);

    // The tighter budget only kicks in for ?fresh=1 (cache-bypassing) calls.
    Route::middleware(ThrottleFreshFeed::class)->group(function (): void {
        Route::get('feed', [FeedController::class, 'show']);
        Route::get('stream', [StreamController::class, 'show']);
    });
});
