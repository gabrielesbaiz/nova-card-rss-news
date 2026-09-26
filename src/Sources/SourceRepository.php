<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Sources;

use Closure;
use Gabrielesbaiz\NovaCardRssNews\Contracts\SourceProvider;
use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\SourceNotFound;
use Gabrielesbaiz\NovaCardRssNews\Presets\Preset;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;

/**
 * Merges every configured source provider — preset classes, host classes,
 * closures and inline arrays — into one addressable catalogue.
 */
final class SourceRepository
{
    /** @var Collection<string, Source>|null */
    private ?Collection $resolved = null;

    /**
     * @param  array<int, mixed>  $providers
     */
    public function __construct(
        private readonly Container $container,
        private array $providers = [],
    ) {}

    /**
     * Register an extra provider at runtime (handy in tests and in host code).
     */
    public function extend(mixed $provider): self
    {
        $this->providers[] = $provider;
        $this->resolved = null;

        return $this;
    }

    public function flush(): void
    {
        $this->resolved = null;
    }

    /**
     * Every enabled source, keyed by source key. Later providers win.
     *
     * @return Collection<string, Source>
     */
    public function all(): Collection
    {
        if ($this->resolved !== null) {
            return $this->resolved;
        }

        /** @var Collection<string, Source> $sources */
        $sources = new Collection;

        foreach ($this->providers as $provider) {
            foreach ($this->expand($provider) as $source) {
                if ($source->url === '' || ! $source->enabled) {
                    continue;
                }

                $sources->put($source->key, $source);
            }
        }

        return $this->resolved = $sources;
    }

    public function find(string $key): ?Source
    {
        return $this->all()->get($key);
    }

    public function findOrFail(string $key): Source
    {
        return $this->find($key) ?? throw SourceNotFound::key($key);
    }

    /**
     * @param  array<int, string>  $keys
     * @return Collection<string, Source>
     */
    public function only(array $keys): Collection
    {
        return $this->all()->only($keys);
    }

    /**
     * @param  array<int, string>  $categories
     * @return Collection<string, Source>
     */
    public function inCategories(array $categories): Collection
    {
        return $this->all()->filter(
            static fn (Source $source): bool => in_array($source->categoryKey, $categories, true)
        );
    }

    /**
     * The catalogue grouped for the source picker.
     *
     * @param  array<int, string>  $onlyCategories
     * @return array<int, array{key: string, label: string, sources: array<int, array{name: string, title: string}>}>
     */
    public function grouped(array $onlyCategories = []): array
    {
        return $this->all()
            ->when($onlyCategories !== [], fn (Collection $all): Collection => $all->filter(
                static fn (Source $source): bool => in_array($source->categoryKey, $onlyCategories, true)
            ))
            ->groupBy(static fn (Source $source): string => $source->categoryKey)
            ->map(fn (Collection $group, string $categoryKey): array => [
                'key' => $categoryKey,
                'label' => $group->first()->categoryLabel(),
                'sources' => $group
                    ->map(static fn (Source $source): array => ['name' => $source->key, 'title' => $source->label()])
                    ->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)
                    ->values()
                    ->all(),
            ])
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values()
            ->all();
    }

    /**
     * Normalize one configured provider entry into a list of sources.
     *
     * @return iterable<Source>
     */
    private function expand(mixed $provider): iterable
    {
        if ($provider instanceof Closure) {
            return $this->expand($provider());
        }

        if ($provider instanceof SourceProvider) {
            return $provider->sources();
        }

        if ($provider instanceof Source) {
            return [$provider];
        }

        if (is_string($provider) && class_exists($provider)) {
            $instance = $this->container->make($provider);

            return $instance instanceof SourceProvider ? $instance->sources() : [];
        }

        if ($provider instanceof Collection) {
            return $this->expand($provider->all());
        }

        if (is_array($provider)) {
            // Either the v2 nested shape, or a flat list of sources / providers.
            if (isset($provider['categories']) && is_array($provider['categories'])) {
                return Preset::sourcesFrom($provider['categories']);
            }

            if (isset($provider['url'], $provider['key'])) {
                return [Source::fromArray((string) $provider['key'], $provider)];
            }

            $sources = [];

            foreach ($provider as $key => $entry) {
                if (is_array($entry) && isset($entry['url']) && is_string($key)) {
                    $sources[] = Source::fromArray($key, $entry, (string) ($entry['category'] ?? 'general'));

                    continue;
                }

                foreach ($this->expand($entry) as $source) {
                    $sources[] = $source;
                }
            }

            return $sources;
        }

        return [];
    }
}
