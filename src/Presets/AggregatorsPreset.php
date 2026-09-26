<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * News aggregators.
 */
final class AggregatorsPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'aggregatori' => [
                'label' => 'Aggregatori',
                'sources' => [
                    'google_news_it' => ['title' => 'Google News Italia', 'url' => 'https://news.google.com/rss?hl=it&gl=IT&ceid=IT:it'],
                ],
            ],
        ];
    }
}
