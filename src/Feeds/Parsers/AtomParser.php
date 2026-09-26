<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers;

use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\DateParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\HtmlSanitizer;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\ImageExtractor;
use SimpleXMLElement;

/**
 * Atom 1.0: <feed><entry>. Links live in href attributes.
 */
final class AtomParser extends XmlParser
{
    public function key(): string
    {
        return 'atom';
    }

    protected function rootElement(): string
    {
        return 'feed';
    }

    public function parse(string $body): array
    {
        $root = $this->root($body);

        if ($root === null) {
            return ['title' => null, 'site_url' => null, 'items' => []];
        }

        $items = [];

        foreach ($root->entry as $entry) {
            $summary = (string) $entry->summary;
            $content = (string) $entry->content;

            $categories = [];

            foreach ($entry->category as $category) {
                $term = trim((string) ($category['term'] ?? ''));

                if ($term !== '') {
                    $categories[] = $term;
                }
            }

            $items[] = new FeedItem(
                title: HtmlSanitizer::title((string) $entry->title),
                link: $this->link($entry),
                summary: HtmlSanitizer::text($summary !== '' ? $summary : $content, 400),
                publishedAt: DateParser::parse((string) $entry->published ?: (string) $entry->updated),
                imageUrl: ImageExtractor::fromXml($entry, $content, $summary),
                author: HtmlSanitizer::title((string) ($entry->author->name ?? '')) ?: null,
                categories: array_slice(array_unique($categories), 0, 5),
            );
        }

        return [
            'title' => HtmlSanitizer::title((string) $root->title) ?: null,
            'site_url' => $this->link($root) ?: null,
            'items' => $items,
        ];
    }

    private function link(SimpleXMLElement $node): string
    {
        $fallback = '';

        foreach ($node->link as $candidate) {
            $rel = (string) ($candidate['rel'] ?? '');
            $href = (string) ($candidate['href'] ?? '');

            if ($href === '') {
                continue;
            }

            if ($rel === '' || $rel === 'alternate') {
                return $href;
            }

            if ($fallback === '') {
                $fallback = $href;
            }
        }

        return $fallback;
    }
}
