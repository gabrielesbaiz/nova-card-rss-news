<?php

namespace Gabrielesbaiz\NovaCardRssNews\Http\Controllers;

class SourcesController
{
    /**
     * Return the list of available RSS sources grouped by category.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $categories = config('nova-card-rss-news.categories', []);

        $payload = collect($categories)
            ->map(fn (array $category, string $key): array => [
                'key' => $key,
                'label' => $category['label'] ?? $key,
                'sources' => collect($category['sources'] ?? [])
                    ->map(fn (array $source, string $name): array => [
                        'name' => $name,
                        'title' => $source['title'] ?? $name,
                    ])
                    ->values(),
            ])
            ->filter(fn (array $cat): bool => count($cat['sources']) > 0)
            ->values();

        return response()->json(['categories' => $payload]);
    }
}
