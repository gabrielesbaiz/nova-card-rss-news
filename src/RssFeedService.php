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

            // Atom feeds use a <feed> root with <entry> children; RSS 2.0 uses
            // <rss><channel><item>. Detect and parse accordingly.
            if ($rss->getName() === 'feed') {
                return self::parseAtomEntries($rss);
            }

            return self::parseRssItems($rss);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Parse RSS 2.0 <item> elements.
     *
     * @param  SimpleXMLElement $rss
     * @return array
     */
    private static function parseRssItems(SimpleXMLElement $rss): array
    {
        $items = [];

        foreach ($rss->channel->item as $item) {
            $items[] = [
                'title' => self::cleanTitle((string) $item->title),
                'link' => (string) $item->link,
                'description' => self::cleanDescription((string) $item->description),
                'pubDate' => (string) $item->pubDate,
            ];
        }

        return $items;
    }

    /**
     * Parse Atom <entry> elements.
     *
     * @param  SimpleXMLElement $feed
     * @return array
     */
    private static function parseAtomEntries(SimpleXMLElement $feed): array
    {
        $items = [];

        foreach ($feed->entry as $entry) {
            // Atom links are href attributes; prefer rel="alternate" (or no rel).
            $link = '';
            foreach ($entry->link as $candidate) {
                $rel = (string) $candidate['rel'];

                if ($rel === '' || $rel === 'alternate') {
                    $link = (string) $candidate['href'];
                    break;
                }

                if ($link === '') {
                    $link = (string) $candidate['href'];
                }
            }

            $description = (string) $entry->summary !== ''
                ? (string) $entry->summary
                : (string) $entry->content;

            $pubDate = (string) $entry->published !== ''
                ? (string) $entry->published
                : (string) $entry->updated;

            $items[] = [
                'title' => self::cleanTitle((string) $entry->title),
                'link' => $link,
                'description' => self::cleanDescription($description),
                'pubDate' => $pubDate,
            ];
        }

        return $items;
    }

    /**
     * Decode HTML entities in a feed title.
     *
     * @param  string $title
     * @return string
     */
    private static function cleanTitle(string $title): string
    {
        return trim(html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * Strip images / markup and decode entities in a feed description.
     *
     * @param  string $description
     * @return string
     */
    private static function cleanDescription(string $description): string
    {
        $clean = preg_replace('/<img[^>]+>/i', '', $description);
        $clean = strip_tags($clean);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($clean);
    }
}
