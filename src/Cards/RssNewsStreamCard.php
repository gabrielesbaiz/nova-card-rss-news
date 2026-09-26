<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Cards;

use Gabrielesbaiz\NovaCardRssNews\Cards\Concerns\InteractsWithFeedCard;
use Laravel\Nova\Card;

/**
 * Several feeds merged into one chronological stream, each item badged with
 * the source it came from.
 */
class RssNewsStreamCard extends Card
{
    use InteractsWithFeedCard;

    public $width = 'full';

    public function __construct($component = null)
    {
        parent::__construct($component);

        $this->withMeta($this->defaultMeta() + [
            'limit' => 20,
            'sources' => [],
            'categories' => [],
        ]);
    }

    public function component(): string
    {
        return 'nova-card-rss-news-stream';
    }

    /**
     * @param  array<int, string>  $sourceKeys
     */
    public function sources(array $sourceKeys): static
    {
        return $this->withMeta(['sources' => array_values($sourceKeys)]);
    }

    /**
     * Pull in every source of these categories.
     *
     * @param  array<int, string>  $categories
     */
    public function categories(array $categories): static
    {
        return $this->withMeta(['categories' => array_values($categories)]);
    }
}
