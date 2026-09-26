<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Cards;

use Gabrielesbaiz\NovaCardRssNews\Cards\Concerns\InteractsWithFeedCard;
use Laravel\Nova\Card;

/**
 * One card, one feed.
 */
class RssNewsCard extends Card
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
        return 'nova-card-rss-news';
    }

    /**
     * Pick a source from the catalogue by key.
     */
    public function source(string $sourceKey): static
    {
        return $this->withMeta(['source_key' => $sourceKey, 'feed' => null]);
    }
}
