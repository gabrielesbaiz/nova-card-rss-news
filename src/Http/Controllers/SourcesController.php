<?php

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

use Illuminate\Support\Facades\File;

class SourcesController
{
    /**
     * Return the list of available RSS sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $jsonPath = __DIR__ . '/../../Data/rss_sources.json';

        if (! File::exists($jsonPath)) {
            return response()->json(['sources' => []]);
        }

        $data = json_decode(File::get($jsonPath), true);

        $sources = collect($data['sources'] ?? [])
            ->map(fn (array $source): array => [
                'name' => $source['name'],
                'title' => $source['title'],
            ])
            ->values();

        return response()->json(['sources' => $sources]);
    }
}
