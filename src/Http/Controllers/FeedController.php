<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedException;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Http\Requests\FeedRequest;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Http\JsonResponse;

class FeedController
{
    public function show(FeedRequest $request, SourceRepository $sources, FeedManager $feeds): JsonResponse
    {
        try {
            $source = $request->source($sources);
        } catch (FeedException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        try {
            $feed = $feeds->get($source, $request->limit(), $request->fresh());
        } catch (FeedException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json($feed->toArray());
    }
}
