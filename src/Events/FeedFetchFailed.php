<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Events;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Throwable;

final class FeedFetchFailed
{
    public function __construct(
        public readonly Source $source,
        public readonly Throwable $exception,
        public readonly bool $servedStale = false,
    ) {}
}
