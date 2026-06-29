<?php

namespace Gabrielesbaiz\NovaCardRssNews;

use Exception;
use SimpleXMLElement;

class RssFeedService
{
    /**
     * Get the configured RSS feed by source name.
     *
     * @param  string $sourceName
     * @return array|null
     */
    public static function getRssFeed(string $sourceName): ?array
    {
        $source = self::findSource($sourceName);

        if (! $source) {
            return null;
        }

        return [
            'title' => $source['title'],
            'url' => $source['url'],
            'feed' => self::fetchRssFeed($source['url']),
        ];
    }

    /**
     * Look up a source across all configured categories.
     *
     * @param  string $name
     * @return array|null
     */
    private static function findSource(string $name): ?array
    {
        $categories = config('nova-card-rss-news.categories', []);

        foreach ($categories as $category) {
            $sources = $category['sources'] ?? [];

            if (isset($sources[$name])) {
                return $sources[$name];
            }
        }

        return null;
    }

    /**
     * Fetch and parse a remote RSS feed.
     *
     * @param  string $url
     * @return array|null
     */
    private static function fetchRssFeed(string $url): ?array
    {
        try {
            $rssContent = file_get_contents($url);

            if (! $rssContent) {
                return null;
            }

            $rss = new SimpleXMLElement($rssContent, LIBXML_NOCDATA);

            $items = [];

            foreach ($rss->channel->item as $item) {
                $cleanDescription = preg_replace('/<img[^>]+>/i', '', (string) $item->description);
                $cleanDescription = strip_tags($cleanDescription);
                $cleanDescription = html_entity_decode($cleanDescription, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $title = html_entity_decode((string) $item->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');

                $items[] = [
                    'title' => trim($title),
                    'link' => (string) $item->link,
                    'description' => trim($cleanDescription),
                    'pubDate' => (string) $item->pubDate,
                ];
            }

            return $items;
        } catch (Exception $e) {
            return null;
        }
    }
}
