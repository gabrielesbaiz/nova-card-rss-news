<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Data;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * A single normalized entry, whatever feed dialect it came from.
 *
 * @implements Arrayable<string, mixed>
 */
final class FeedItem implements Arrayable, JsonSerializable
{
    /**
     * @param  array<int, string>  $categories
     */
    public function __construct(
        public readonly string $title,
        public readonly string $link,
        public readonly string $summary = '',
        public readonly ?CarbonImmutable $publishedAt = null,
        public readonly ?string $imageUrl = null,
        public readonly ?string $author = null,
        public readonly array $categories = [],
        public readonly ?string $sourceKey = null,
        public readonly ?string $sourceTitle = null,
        public readonly ?string $sourceSite = null,
    ) {}

    /**
     * Stable identity used by the client for read / bookmark state.
     */
    public function id(): string
    {
        return sha1($this->link !== '' ? $this->link : $this->title);
    }

    public function timestamp(): int
    {
        return $this->publishedAt?->getTimestamp() ?? 0;
    }

    public function forSource(Source $source): self
    {
        return new self(
            title: $this->title,
            link: $this->link,
            summary: $this->summary,
            publishedAt: $this->publishedAt,
            imageUrl: $this->imageUrl,
            author: $this->author,
            categories: $this->categories,
            sourceKey: $source->key,
            sourceTitle: $source->label(),
            sourceSite: $source->site(),
        );
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(
            title: (string) ($payload['title'] ?? ''),
            link: (string) ($payload['link'] ?? ''),
            summary: (string) ($payload['summary'] ?? ''),
            publishedAt: ($payload['published_at'] ?? null) !== null
                ? CarbonImmutable::parse((string) $payload['published_at'])
                : null,
            imageUrl: $payload['image_url'] ?? null,
            author: $payload['author'] ?? null,
            categories: array_values((array) ($payload['categories'] ?? [])),
            sourceKey: $payload['source_key'] ?? null,
            sourceTitle: $payload['source_title'] ?? null,
            sourceSite: $payload['source_site'] ?? null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id(),
            'title' => $this->title,
            'link' => $this->link,
            'summary' => $this->summary,
            'published_at' => $this->publishedAt?->toIso8601String(),
            'image_url' => $this->imageUrl,
            'author' => $this->author,
            'categories' => $this->categories,
            'source_key' => $this->sourceKey,
            'source_title' => $this->sourceTitle,
            'source_site' => $this->sourceSite,
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
