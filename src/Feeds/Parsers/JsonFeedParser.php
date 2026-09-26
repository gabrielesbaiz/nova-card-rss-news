<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers;

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedParser;
use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\DateParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\HtmlSanitizer;

/**
 * JSON Feed 1.x — https://jsonfeed.org/version/1.1
 */
final class JsonFeedParser implements FeedParser
{
    public function key(): string
    {
        return 'json';
    }

    public function supports(string $body): bool
    {
        $decoded = $this->decode($body);

        return $decoded !== null && isset($decoded['items']) && is_array($decoded['items']);
    }

    public function parse(string $body): array
    {
        $decoded = $this->decode($body);

        if ($decoded === null) {
            return ['title' => null, 'site_url' => null, 'items' => []];
        }

        $items = [];

        foreach ($decoded['items'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            $summary = (string) ($entry['summary'] ?? $entry['content_text'] ?? $entry['content_html'] ?? '');

            $items[] = new FeedItem(
                title: HtmlSanitizer::title((string) ($entry['title'] ?? '')),
                link: (string) ($entry['url'] ?? $entry['external_url'] ?? ''),
                summary: HtmlSanitizer::text($summary, 400),
                publishedAt: DateParser::parse($entry['date_published'] ?? $entry['date_modified'] ?? null),
                imageUrl: $entry['image'] ?? $entry['banner_image'] ?? null,
                author: $entry['author']['name'] ?? ($entry['authors'][0]['name'] ?? null),
                categories: array_slice(array_values((array) ($entry['tags'] ?? [])), 0, 5),
            );
        }

        return [
            'title' => isset($decoded['title']) ? HtmlSanitizer::title((string) $decoded['title']) : null,
            'site_url' => $decoded['home_page_url'] ?? null,
            'items' => $items,
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function decode(string $body): ?array
    {
        $trimmed = ltrim($body, "\xEF\xBB\xBF \t\n\r");

        if ($trimmed === '' || $trimmed[0] !== '{') {
            return null;
        }

        $decoded = json_decode($trimmed, true);

        return is_array($decoded) ? $decoded : null;
    }
}
