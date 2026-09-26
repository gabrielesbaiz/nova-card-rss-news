<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers;

use Gabrielesbaiz\NovaCardRssNews\Data\FeedItem;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\DateParser;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\HtmlSanitizer;
use Gabrielesbaiz\NovaCardRssNews\Feeds\Support\ImageExtractor;

/**
 * RSS 1.0 / RDF: <rdf:RDF> with <item> siblings of <channel>.
 */
final class RdfParser extends XmlParser
{
    private const DC_NS = 'http://purl.org/dc/elements/1.1/';

    public function key(): string
    {
        return 'rdf';
    }

    protected function rootElement(): string
    {
        return 'RDF';
    }

    public function parse(string $body): array
    {
        $root = $this->root($body);

        if ($root === null) {
            return ['title' => null, 'site_url' => null, 'items' => []];
        }

        $items = [];

        foreach ($root->item as $item) {
            $description = (string) $item->description;

            $items[] = new FeedItem(
                title: HtmlSanitizer::title((string) $item->title),
                link: trim((string) $item->link),
                summary: HtmlSanitizer::text($description, 400),
                publishedAt: DateParser::parse($this->namespaced($item, self::DC_NS, 'date')),
                imageUrl: ImageExtractor::fromXml($item, $description),
                author: HtmlSanitizer::title($this->namespaced($item, self::DC_NS, 'creator')) ?: null,
            );
        }

        return [
            'title' => isset($root->channel) ? HtmlSanitizer::title((string) $root->channel->title) ?: null : null,
            'site_url' => isset($root->channel) ? trim((string) $root->channel->link) ?: null : null,
            'items' => $items,
        ];
    }
}
