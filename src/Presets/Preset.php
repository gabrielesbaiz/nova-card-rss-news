<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

use Gabrielesbaiz\NovaCardRssNews\Contracts\SourceProvider;
use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Illuminate\Support\Collection;

/**
 * A curated, shippable bundle of sources. Subclasses only declare data.
 */
abstract class Preset implements SourceProvider
{
    /**
     * Category tree in the shape used by the config file:
     * ['category_key' => ['label' => ..., 'sources' => ['key' => ['title','url']]]].
     *
     * @return array<string, array{label?: string, sources: array<string, array<string, mixed>>}>
     */
    abstract public static function categories(): array;

    public function sources(): Collection
    {
        return self::sourcesFrom(static::categories());
    }

    /**
     * @param  array<string, array<string, mixed>>  $categories
     * @return Collection<int, Source>
     */
    public static function sourcesFrom(array $categories): Collection
    {
        $sources = new Collection;

        foreach ($categories as $categoryKey => $category) {
            $label = $category['label'] ?? "nova-card-rss-news::sources.{$categoryKey}";

            foreach ($category['sources'] ?? [] as $key => $attributes) {
                $sources->push(Source::fromArray((string) $key, (array) $attributes, (string) $categoryKey, $label));
            }
        }

        return $sources;
    }
}
