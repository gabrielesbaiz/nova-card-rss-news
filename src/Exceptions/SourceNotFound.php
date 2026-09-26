<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Exceptions;

class SourceNotFound extends FeedException
{
    public static function key(string $key): self
    {
        return new self("No RSS source configured for key [{$key}].");
    }
}
