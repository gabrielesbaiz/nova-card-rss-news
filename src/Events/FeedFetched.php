<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Events;

use Gabrielesbaiz\NovaCardRssNews\Data\Feed;
use Gabrielesbaiz\NovaCardRssNews\Data\Source;

final class FeedFetched
{
    public function __construct(
        public readonly Source $source,
        public readonly Feed $feed,
        public readonly float $durationMs = 0.0,
        public readonly bool $notModified = false,
    ) {}
}
