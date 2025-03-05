<?php

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Gabrielesbaiz\NovaCardRssNews\RssFeedService;

class NewsController
{
    public function index(Request $request)
    {
        $sourceKey = $request->get('source_key', 'motor1');

        $cacheKey = "news-rss::{$sourceKey}";

        $data = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($sourceKey) {
            return RssFeedService::getRssFeed($sourceKey);
        });

        if (! $data) {
            return response()->json(['error' => 'Invalid RSS source or no data available'], 404);
        }

        return response()->json($data);
    }
}
