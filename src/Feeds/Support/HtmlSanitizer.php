<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Support;

/**
 * Turns feed markup into plain, safe text for the card.
 */
final class HtmlSanitizer
{
    public static function title(string $value): string
    {
        return trim(self::decode(strip_tags($value)));
    }

    public static function text(string $value, ?int $limit = null): string
    {
        $clean = preg_replace('/<(img|script|style|iframe)[^>]*>.*?(<\/\1>)?/is', '', $value) ?? $value;
        $clean = preg_replace('/\s+/u', ' ', strip_tags($clean)) ?? $clean;
        $clean = trim(self::decode($clean));

        if ($limit !== null && $limit > 0 && mb_strlen($clean) > $limit) {
            $clean = rtrim(mb_substr($clean, 0, $limit), " \t\n\r\0\x0B.,;:").'…';
        }

        return $clean;
    }

    private static function decode(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
