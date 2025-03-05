<?php

namespace Gabrielesbaiz\NovaCardRssNews;

use Laravel\Nova\Card;

class NovaCardRssNews extends Card
{
    /**
     * The width of the card (1/3, 1/2, or full).
     *
     * @var string
     */
    public $width = '1/2';

    /**
     * Constructor with default metadata.
     */
    public function __construct()
    {
        parent::__construct();

        $this->withMeta(['source_key' => 'motor1']);
    }

    /**
     * Get the component name for the element.
     *
     * @return string
     */
    public function component()
    {
        return 'nova-card-rss-news';
    }

    /**
     * Set the RSS source key.
     *
     * @param  string $sourceKey
     * @return $this
     */
    public function source(string $sourceKey)
    {
        return $this->withMeta(['source_key' => $sourceKey]);
    }
}
