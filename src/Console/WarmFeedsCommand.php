<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Support\Facades\Log;
use Throwable;

class WarmFeedsCommand extends CheckFeedsCommand
{
    protected $signature = 'nova-rss:warm
        {--source=* : Limit the warm-up to these source keys}
        {--category=* : Limit the warm-up to these category keys}
        {--strict : Exit non-zero when any single source fails}';

    protected $description = 'Pre-fetch every configured RSS source so dashboards load warm';

    public function handle(SourceRepository $sources, FeedManager $feeds): int
    {
        $selected = $this->select($sources);
        $warmed = 0;
        $failed = 0;

        $this->components->info("Warming {$selected->count()} sources…");

        foreach ($selected as $source) {
            /** @var Source $source */
            try {
                $feed = $feeds->get($source, null, fresh: true);
                $warmed++;
                $this->components->twoColumnDetail($source->key, count($feed->items).' items');
            } catch (Throwable $e) {
                $failed++;
                $this->components->twoColumnDetail($source->key, '<fg=red>'.$e->getMessage().'</>');

                // A scheduler keeps the exit code and discards the output, so
                // the name of the source that went down has to reach the log
                // or nobody can tell which publisher to blame.
                Log::warning("nova-rss: could not warm [{$source->key}]: {$e->getMessage()}");
            }
        }

        if ($failed === 0) {
            return self::SUCCESS;
        }

        $this->components->warn("{$failed} of {$selected->count()} sources could not be warmed.");

        // A cache filled from 45 of 46 publishers is warm, and third-party
        // feeds time out as a matter of course. Failing the command for one of
        // them turns a scheduled warm-up into a nightly false alarm, so only a
        // total failure — which points at this application's network or
        // configuration rather than at a publisher — is reported as one.
        if ($this->option('strict')) {
            return self::FAILURE;
        }

        return $warmed > 0 ? self::SUCCESS : self::FAILURE;
    }
}
