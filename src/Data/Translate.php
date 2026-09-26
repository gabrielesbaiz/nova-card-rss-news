<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Data;

/**
 * Resolves a label that may either be a literal string or a translation key.
 */
final class Translate
{
    public static function line(string $value): string
    {
        if (! str_contains($value, '::') && ! str_starts_with($value, 'nova-card-rss-news.')) {
            return $value;
        }

        $translated = trans($value);

        return is_string($translated) && $translated !== $value ? $translated : self::humanize($value);
    }

    private static function humanize(string $value): string
    {
        $tail = (string) str($value)->afterLast('.')->afterLast('::');

        return (string) str($tail)->replace(['-', '_'], ' ')->title();
    }
}
