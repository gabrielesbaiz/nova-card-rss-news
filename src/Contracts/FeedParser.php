<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Contracts;

use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;

interface FeedParser
{
    /**
     * Short identifier reported back to the client and to nova-rss:check.
     */
    public function key(): string;

    public function supports(string $body): bool;

    /**
     * @return array{title: ?string, site_url: ?string, items: array<int, FeedItem>}
     */
    public function parse(string $body): array;
}
