<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian business and finance desks.
 */
final class ItEconomyPreset extends Preset
{
    public static function categories(): array
    {
        return [
            'economia' => [
                'label' => 'Economia / Finanza',
                'sources' => [
                    'il_sole_24_ore_finanza' => ['title' => 'Il Sole 24 ORE — Finanza', 'url' => 'https://www.ilsole24ore.com/rss/finanza.xml'],
                    'il_sole_24_ore_italia' => ['title' => 'Il Sole 24 ORE — Italia', 'url' => 'https://www.ilsole24ore.com/rss/italia.xml'],
                    'il_sole_24_ore_norme_tributi' => ['title' => 'Il Sole 24 ORE — Norme & Tributi', 'url' => 'https://www.ilsole24ore.com/rss/norme-e-tributi.xml'],
                    'il_sole_24_ore_risparmio' => ['title' => 'Il Sole 24 ORE — Risparmio', 'url' => 'https://www.ilsole24ore.com/rss/risparmio.xml'],
                ],
            ],
        ];
    }
}
