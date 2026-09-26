<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * The neutral, international default: a handful of stable feeds so the card
 * shows something meaningful the moment the package is installed.
 */
final class StarterPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'world' => [
                'sources' => [
                    'bbc_world' => ['title' => 'BBC World News', 'url' => 'https://feeds.bbci.co.uk/news/world/rss.xml'],
                    'npr_news' => ['title' => 'NPR News', 'url' => 'https://feeds.npr.org/1001/rss.xml'],
                    'al_jazeera' => ['title' => 'Al Jazeera', 'url' => 'https://www.aljazeera.com/xml/rss/all.xml'],
                ],
            ],
            'technology' => [
                'sources' => [
                    'hacker_news' => ['title' => 'Hacker News', 'url' => 'https://hnrss.org/frontpage'],
                    'the_verge' => ['title' => 'The Verge', 'url' => 'https://www.theverge.com/rss/index.xml'],
                    'ars_technica' => ['title' => 'Ars Technica', 'url' => 'https://feeds.arstechnica.com/arstechnica/index'],
                ],
            ],
            'development' => [
                'sources' => [
                    'laravel_news' => ['title' => 'Laravel News', 'url' => 'https://feed.laravel-news.com/'],
                    'php_watch' => ['title' => 'PHP.Watch', 'url' => 'https://php.watch/feed/php.atom'],
                    'github_blog' => ['title' => 'The GitHub Blog', 'url' => 'https://github.blog/feed/'],
                ],
            ],
        ];
    }
}
