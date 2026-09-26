<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Presets;

/**
 * Italian insurance press, regulators and the insurance desks of the financial papers.
 */
final class ItInsurancePreset extends Preset
{
    public static function categories(): array
    {
        return [
            'assicurazioni_specializzate' => [
                'label' => 'Assicurazioni — Testate specializzate',
                'sources' => [
                    'assinews' => ['title' => 'Assinews', 'url' => 'https://www.assinews.it/feed/'],
                    'insurance_up' => ['title' => 'InsuranceUp', 'url' => 'https://www.insuranceup.it/feed/'],
                    'intermedia_channel' => ['title' => 'Intermedia Channel', 'url' => 'https://www.intermediachannel.it/feed/'],
                ],
            ],
            'assicurazioni_istituzioni' => [
                'label' => 'Assicurazioni — Autorità e istituzioni',
                'sources' => [
                    'ania' => ['title' => 'ANIA', 'url' => 'https://www.ania.it/feed/'],
                    'ivass' => ['title' => 'IVASS', 'url' => 'https://www.ivass.it/util/index.rss.html?lingua=it'],
                ],
            ],
            'assicurazioni_economiche' => [
                'label' => 'Assicurazioni — Sezioni da testate economiche',
                'sources' => [
                    'firstonline_assicurazioni' => ['title' => 'FIRSTonline — Assicurazioni', 'url' => 'https://www.firstonline.info/tag/assicurazioni/feed/'],
                    'il_sole_24_ore_finanza_assicurazioni' => ['title' => 'Il Sole 24 ORE — Finanza', 'url' => 'https://www.ilsole24ore.com/rss/finanza.xml'],
                ],
            ],
        ];
    }
}
