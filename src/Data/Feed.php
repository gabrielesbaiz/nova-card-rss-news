<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Data;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * A fetched, normalized feed ready to be handed to the card.
 *
 * @implements Arrayable<string, mixed>
 */
final class Feed implements Arrayable, JsonSerializable
{
    /**
     * @param  array<int, FeedItem>  $items
     */
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $feedUrl,
        public readonly array $items = [],
        public readonly ?string $siteUrl = null,
        public readonly ?CarbonImmutable $fetchedAt = null,
        public readonly bool $stale = false,
        public readonly ?string $parser = null,
    ) {}

    /**
     * Sort newest first, drop duplicates and apply the limit.
     */
    public function normalized(?int $limit = null): self
    {
        $items = $this->items;

        usort($items, static fn (FeedItem $a, FeedItem $b): int => $b->timestamp() <=> $a->timestamp());

        $seen = [];
        $unique = [];

        foreach ($items as $item) {
            $id = $item->id();

            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $unique[] = $item;
        }

        if ($limit !== null && $limit > 0) {
            $unique = array_slice($unique, 0, $limit);
        }

        return $this->withItems($unique);
    }

    /**
     * @param  array<int, FeedItem>  $items
     */
    public function withItems(array $items): self
    {
        return new self(
            key: $this->key,
            title: $this->title,
            feedUrl: $this->feedUrl,
            items: array_values($items),
            siteUrl: $this->siteUrl,
            fetchedAt: $this->fetchedAt,
            stale: $this->stale,
            parser: $this->parser,
        );
    }

    public function markStale(bool $stale = true): self
    {
        return new self(
            key: $this->key,
            title: $this->title,
            feedUrl: $this->feedUrl,
            items: $this->items,
            siteUrl: $this->siteUrl,
            fetchedAt: $this->fetchedAt,
            stale: $stale,
            parser: $this->parser,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'url' => $this->siteUrl,
            'feed_url' => $this->feedUrl,
            'fetched_at' => $this->fetchedAt?->toIso8601String(),
            'stale' => $this->stale,
            'parser' => $this->parser,
            'items' => array_map(static fn (FeedItem $item): array => $item->toArray(), $this->items),
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
