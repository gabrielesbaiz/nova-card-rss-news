<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Data;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * A single, resolved feed source.
 *
 * @implements Arrayable<string, mixed>
 */
final class Source implements Arrayable, JsonSerializable
{
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $url,
        public readonly string $categoryKey = 'general',
        public readonly ?string $categoryLabel = null,
        public readonly ?string $siteUrl = null,
        public readonly ?int $ttl = null,
        public readonly bool $enabled = true,
        public readonly ?string $parser = null,
    ) {}

    /**
     * Build a source from the array shape used by config files and presets.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function fromArray(string $key, array $attributes, string $categoryKey = 'general', ?string $categoryLabel = null): self
    {
        return new self(
            key: $key,
            title: (string) ($attributes['title'] ?? $key),
            url: (string) ($attributes['url'] ?? ''),
            categoryKey: (string) ($attributes['category'] ?? $categoryKey),
            categoryLabel: $attributes['category_label'] ?? $categoryLabel,
            siteUrl: isset($attributes['site_url']) ? (string) $attributes['site_url'] : null,
            ttl: isset($attributes['ttl']) ? (int) $attributes['ttl'] : null,
            enabled: (bool) ($attributes['enabled'] ?? true),
            parser: isset($attributes['parser']) ? (string) $attributes['parser'] : null,
        );
    }

    /**
     * Human readable title, resolving translation keys when one was given.
     */
    public function label(): string
    {
        return Translate::line($this->title);
    }

    /**
     * Human readable category label, resolving translation keys when one was given.
     */
    public function categoryLabel(): string
    {
        return Translate::line($this->categoryLabel ?? $this->categoryKey);
    }

    /**
     * The website the feed belongs to, guessed from the feed URL when not set.
     */
    public function site(): ?string
    {
        if ($this->siteUrl !== null) {
            return $this->siteUrl;
        }

        $parts = parse_url($this->url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return "{$parts['scheme']}://{$parts['host']}";
    }

    public function with(mixed ...$overrides): self
    {
        return new self(
            key: $overrides['key'] ?? $this->key,
            title: $overrides['title'] ?? $this->title,
            url: $overrides['url'] ?? $this->url,
            categoryKey: $overrides['categoryKey'] ?? $this->categoryKey,
            categoryLabel: $overrides['categoryLabel'] ?? $this->categoryLabel,
            siteUrl: $overrides['siteUrl'] ?? $this->siteUrl,
            ttl: $overrides['ttl'] ?? $this->ttl,
            enabled: $overrides['enabled'] ?? $this->enabled,
            parser: $overrides['parser'] ?? $this->parser,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->label(),
            'url' => $this->url,
            'site_url' => $this->site(),
            'category' => $this->categoryKey,
            'category_label' => $this->categoryLabel(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
