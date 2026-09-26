<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian national newspapers and wire services.
 */
final class ItNewsPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'quotidiani' => [
                'label' => 'Quotidiani nazionali',
                'sources' => [
                    'corriere_home' => ['title' => 'Corriere della Sera (homepage)', 'url' => 'https://xml2.corriereobjects.it/rss/homepage.xml'],
                    'il_fatto_quotidiano' => ['title' => 'Il Fatto Quotidiano', 'url' => 'https://www.ilfattoquotidiano.it/feed/'],
                    'la_stampa' => ['title' => 'La Stampa (copertina)', 'url' => 'https://www.lastampa.it/rss/copertina.xml'],
                    'panorama' => ['title' => 'Panorama', 'url' => 'https://www.panorama.it/feeds/feed.rss'],
                    'repubblica_home' => ['title' => 'Repubblica (homepage)', 'url' => 'https://www.repubblica.it/rss/homepage/rss2.0.xml'],
                    'repubblica_cronaca' => ['title' => 'Repubblica Cronaca', 'url' => 'https://www.repubblica.it/rss/cronaca/rss2.0.xml'],
                    'repubblica_economia' => ['title' => 'Repubblica Economia', 'url' => 'https://www.repubblica.it/rss/economia/rss2.0.xml'],
                    'repubblica_politica' => ['title' => 'Repubblica Politica', 'url' => 'https://www.repubblica.it/rss/politica/rss2.0.xml'],
                ],
            ],
            'agenzie_stampa' => [
                'label' => 'Agenzie di stampa',
                'sources' => [
                    'ansa_all' => ['title' => 'ANSA', 'url' => 'https://www.ansa.it/sito/ansait_rss.xml'],
                    'ansa_cronaca' => ['title' => 'ANSA Cronaca', 'url' => 'https://www.ansa.it/sito/notizie/cronaca/cronaca_rss.xml'],
                    'ansa_cultura' => ['title' => 'ANSA Cultura', 'url' => 'https://www.ansa.it/sito/notizie/cultura/cultura_rss.xml'],
                    'ansa_economia' => ['title' => 'ANSA Economia', 'url' => 'https://www.ansa.it/sito/notizie/economia/economia_rss.xml'],
                    'ansa_sport' => ['title' => 'ANSA Sport', 'url' => 'https://www.ansa.it/sito/notizie/sport/sport_rss.xml'],
                    'ansa_top_news' => ['title' => 'ANSA Top News', 'url' => 'https://www.ansa.it/sito/notizie/topnews/topnews_rss.xml'],
                ],
            ],
        ];
    }
}
