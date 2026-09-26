<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Exceptions;

class UnsupportedFeedFormat extends FeedException
{
    public static function for(string $url): self
    {
        return new self("No parser was able to read the feed at [{$url}].");
    }
}
