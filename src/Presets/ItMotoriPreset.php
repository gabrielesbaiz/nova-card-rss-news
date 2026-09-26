<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian motoring press.
 */
final class ItMotoriPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'motori' => [
                'label' => 'Motori',
                'sources' => [
                    'alvolante' => ['title' => 'Alvolante.it', 'url' => 'https://www.alvolante.it/rss.xml'],
                    'ansa_motori' => ['title' => 'ANSA Motori', 'url' => 'https://www.ansa.it/canale_motori/notizie/motori_rss.xml'],
                    'corriere_motori' => ['title' => 'Corriere Motori', 'url' => 'http://xml2.corriereobjects.it/rss/motori.xml'],
                    'il_sole_24_ore_motori' => ['title' => 'Il Sole 24 ORE Motori', 'url' => 'https://www.ilsole24ore.com/rss/motori.xml'],
                    'motor1' => ['title' => 'Motor1.com', 'url' => 'https://it.motor1.com/rss/articles/all/'],
                    'motori_it' => ['title' => 'Motori.it', 'url' => 'https://www.motori.it/feed'],
                    'quattroruote' => ['title' => 'Quattroruote', 'url' => 'https://www.quattroruote.it/content/quattroruote/it/listino/feeds/newsRss/feed.xml'],
                ],
            ],
        ];
    }
}
