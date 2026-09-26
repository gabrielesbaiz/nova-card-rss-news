<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian travel trade press.
 */
final class ItTravelPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'viaggi' => [
                'label' => 'Viaggi',
                'sources' => [
                    'aerei' => ['title' => 'Aerei', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=16'],
                    'aeroporti' => ['title' => 'Aeroporti', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=17'],
                    'autonoleggio' => ['title' => 'Autonoleggio', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=21'],
                    'beb_agriturismi' => ['title' => 'B&B, Agriturismi & Co.', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=27'],
                    'crociere' => ['title' => 'Crociere', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=18'],
                    'ferrovie' => ['title' => 'Ferrovie', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=20'],
                    'hotel_catene' => ['title' => 'Hotel e Catene Alberghiere', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=28'],
                    'nonsoloturisti' => ['title' => 'Nonsoloturisti', 'url' => 'https://nonsoloturisti.it/feed/'],
                    'si_viaggia' => ['title' => 'SiViaggia', 'url' => 'https://siviaggia.it/feed/'],
                    'tour_operator' => ['title' => 'Tour Operator', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=26'],
                    'traghetti' => ['title' => 'Traghetti', 'url' => 'https://www.masterviaggi.it/feed.php?idcat=19'],
                    'travel_quotidiano' => ['title' => 'TravelQuotidiano', 'url' => 'https://www.travelquotidiano.com/feed/'],
                ],
            ],
        ];
    }
}
