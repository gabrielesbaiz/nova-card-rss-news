<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Data;

/**
 * The raw outcome of an HTTP call to a feed URL.
 */
final class FetchResult
{
    public function __construct(
        public readonly string $url,
        public readonly int $status,
        public readonly string $body = '',
        public readonly ?string $etag = null,
        public readonly ?string $lastModified = null,
        public readonly float $durationMs = 0.0,
    ) {}

    public function notModified(): bool
    {
        return $this->status === 304;
    }
}
