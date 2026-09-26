<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers;

use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\DateParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\HtmlSanitizer;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\ImageExtractor;

/**
 * RSS 2.0: <rss><channel><item>.
 */
final class Rss2Parser extends XmlParser
{
    private const CONTENT_NS = 'http://purl.org/rss/1.0/modules/content/';

    private const DC_NS = 'http://purl.org/dc/elements/1.1/';

    public function key(): string
    {
        return 'rss2';
    }

    protected function rootElement(): string
    {
        return 'rss';
    }

    public function parse(string $body): array
    {
        $root = $this->root($body);

        if ($root === null || ! isset($root->channel)) {
            return ['title' => null, 'site_url' => null, 'items' => []];
        }

        $channel = $root->channel;
        $items = [];

        foreach ($channel->item as $item) {
            $encoded = $this->namespaced($item, self::CONTENT_NS, 'encoded');
            $description = (string) $item->description;
            $author = (string) $item->author ?: $this->namespaced($item, self::DC_NS, 'creator');
            $date = (string) $item->pubDate ?: $this->namespaced($item, self::DC_NS, 'date');

            $categories = [];

            foreach ($item->category as $category) {
                $value = trim((string) $category);

                if ($value !== '') {
                    $categories[] = $value;
                }
            }

            $items[] = new FeedItem(
                title: HtmlSanitizer::title((string) $item->title),
                link: trim((string) $item->link) ?: trim((string) ($item->guid ?? '')),
                summary: HtmlSanitizer::text($description !== '' ? $description : $encoded, 400),
                publishedAt: DateParser::parse($date),
                imageUrl: ImageExtractor::fromXml($item, $description, $encoded),
                author: HtmlSanitizer::title($author) ?: null,
                categories: array_slice(array_unique($categories), 0, 5),
            );
        }

        return [
            'title' => HtmlSanitizer::title((string) $channel->title) ?: null,
            'site_url' => trim((string) $channel->link) ?: null,
            'items' => $items,
        ];
    }
}
