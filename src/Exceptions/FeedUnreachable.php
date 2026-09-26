<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Exceptions;

use Throwable;

class FeedUnreachable extends FeedException
{
    public static function status(string $url, int $status): self
    {
        return new self("Feed [{$url}] responded with HTTP {$status}.");
    }

    public static function transport(string $url, Throwable $previous): self
    {
        return new self("Feed [{$url}] could not be reached: {$previous->getMessage()}", 0, $previous);
    }
}
