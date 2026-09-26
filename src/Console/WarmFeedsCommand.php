<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Throwable;

class WarmFeedsCommand extends CheckFeedsCommand
{
    protected $signature = 'nova-rss:warm
        {--source=* : Limit the warm-up to these source keys}
        {--category=* : Limit the warm-up to these category keys}';

    protected $description = 'Pre-fetch every configured RSS source so dashboards load warm';

    public function handle(SourceRepository $sources, FeedManager $feeds): int
    {
        $selected = $this->select($sources);
        $failed = 0;

        $this->components->info("Warming {$selected->count()} sources…");

        foreach ($selected as $source) {
            /** @var Source $source */
            try {
                $feed = $feeds->get($source, null, fresh: true);
                $this->components->twoColumnDetail($source->key, count($feed->items).' items');
            } catch (Throwable $e) {
                $failed++;
                $this->components->twoColumnDetail($source->key, '<fg=red>'.$e->getMessage().'</>');
            }
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
