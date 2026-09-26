<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Support;

use Carbon\CarbonImmutable;
use Throwable;

/**
 * Feed dates are famously inconsistent; parse leniently, never throw.
 */
final class DateParser
{
    public static function parse(?string $value): ?CarbonImmutable
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value)->utc();
        } catch (Throwable) {
            $timestamp = strtotime($value);

            return $timestamp === false ? null : CarbonImmutable::createFromTimestampUTC($timestamp);
        }
    }
}
