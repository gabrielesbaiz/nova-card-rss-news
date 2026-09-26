<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Http\Requests\FeedRequest;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Http\JsonResponse;

class StreamController
{
    public function show(FeedRequest $request, SourceRepository $sources, FeedManager $feeds): JsonResponse
    {
        /** @var array<int, string> $keys */
        $keys = (array) $request->input('sources', []);
        /** @var array<int, string> $categories */
        $categories = (array) $request->input('categories', []);

        $selected = $sources->only($keys);

        if ($categories !== []) {
            $selected = $selected->merge($sources->inCategories($categories));
        }

        if ($selected->isEmpty()) {
            return response()->json(['message' => 'No known sources were requested.'], 404);
        }

        $feed = $feeds->stream($selected, $request->limit(), $request->fresh());

        return response()->json($feed->toArray());
    }
}
