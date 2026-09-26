<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Cards;

use Gabrielesbaiz\NovaCardRssNews\Cards\Concerns\InteractsWithFeedCard;
use Laravel\Nova\Card;

/**
 * Same card, with a source picker in the header. The choice is remembered
 * per browser and per card instance.
 */
class RssNewsSelectCard extends Card
{
    use InteractsWithFeedCard;

    public $width = '1/2';

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->withMeta($this->defaultMeta());
    }

    public function component(): string
    {
        return 'nova-card-rss-news-select';
    }

    /**
     * Source selected the first time the card is shown.
     */
    public function defaultSource(string $sourceKey): static
    {
        return $this->withMeta(['source_key' => $sourceKey]);
    }

    /**
     * Restrict the picker to these category keys.
     *
     * @param  array<int, string>  $categories
     */
    public function categories(array $categories): static
    {
        return $this->withMeta(['categories' => array_values($categories)]);
    }

    /**
     * Remember the selection in localStorage (on by default).
     */
    public function remember(bool $remember = true): static
    {
        return $this->withMeta(['remember' => $remember]);
    }
}
