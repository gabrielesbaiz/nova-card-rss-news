<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian sports press.
 */
final class ItSportPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'sport' => [
                'label' => 'Sport',
                'sources' => [
                    'gazzetta' => ['title' => 'Gazzetta dello Sport', 'url' => 'https://www.gazzetta.it/rss/home.xml'],
                ],
            ],
        ];
    }
}
