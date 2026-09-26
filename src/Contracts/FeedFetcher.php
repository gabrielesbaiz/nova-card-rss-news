<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Contracts;

use Gabrielesbaiz\NovaCardRssNews\Data\FetchResult;

interface FeedFetcher
{
    /**
     * Fetch a URL, optionally revalidating with a stored validator.
     */
    public function fetch(string $url, ?string $etag = null, ?string $lastModified = null): FetchResult;
}
