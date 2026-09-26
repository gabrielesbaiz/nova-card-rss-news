<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Support;

use SimpleXMLElement;

/**
 * Digs a usable thumbnail out of an entry, trying the common conventions in order.
 */
final class ImageExtractor
{
    public static function fromXml(SimpleXMLElement $node, string ...$htmlCandidates): ?string
    {
        $media = $node->children('http://search.yahoo.com/mrss/');

        foreach (['content', 'thumbnail'] as $tag) {
            if (isset($media->{$tag})) {
                foreach ($media->{$tag} as $candidate) {
                    // Attributes of a namespaced child are themselves outside
                    // that namespace, so read them through attributes().
                    $attributes = $candidate->attributes();
                    $url = (string) ($attributes['url'] ?? '');

                    if ($url !== '' && self::looksLikeImage($url, (string) ($attributes['type'] ?? ''))) {
                        return $url;
                    }
                }
            }
        }

        foreach ($node->enclosure ?? [] as $enclosure) {
            $url = (string) ($enclosure['url'] ?? '');
            $type = (string) ($enclosure['type'] ?? '');

            if ($url !== '' && self::looksLikeImage($url, $type)) {
                return $url;
            }
        }

        if (isset($node->image) && (string) $node->image !== '') {
            return (string) $node->image;
        }

        foreach ($htmlCandidates as $html) {
            $found = self::fromHtml($html);

            if ($found !== null) {
                return $found;
            }
        }

        return null;
    }

    public static function fromHtml(string $html): ?string
    {
        if ($html === '' || ! preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $matches)) {
            return null;
        }

        $url = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return str_starts_with($url, 'http') || str_starts_with($url, '//') ? $url : null;
    }

    private static function looksLikeImage(string $url, string $type = ''): bool
    {
        if ($type !== '') {
            return str_starts_with($type, 'image/');
        }

        return (bool) preg_match('/\.(jpe?g|png|gif|webp|avif)(\?|$)/i', $url);
    }
}
