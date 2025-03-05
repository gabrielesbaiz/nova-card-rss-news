<?php

namespace Gabrielesbaiz\NovaCardRssNews;

use Exception;
use SimpleXMLElement;
use Illuminate\Support\Facades\File;

class RssFeedService
{
    public static function getRssFeed(string $sourceName): ?array
    {
        $jsonPath = __DIR__ . '/Data/rss_sources.json';

        if (! File::exists($jsonPath)) {
            return null;
        }

        $jsonContent = File::get($jsonPath);

        $data = json_decode($jsonContent, true);

        if (! isset($data['sources'])) {
            return null;
        }

        $source = collect($data['sources'])->firstWhere('name', $sourceName);

        if (! $source) {
            return null;
        }

        return [
            'title' => $source['title'],
            'url' => $source['url'],
            'feed' => self::fetchRssFeed($source['url']),
        ];
    }

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
                $items[] = [
                    'title' => (string) $item->title,
                    'link' => (string) $item->link,
                    'description' => (string) $item->description,
                    'pubDate' => (string) $item->pubDate,
                ];
            }

            return $items;
        } catch (Exception $e) {
            return null;
        }
    }
}
