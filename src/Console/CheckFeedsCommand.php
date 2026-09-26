<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Feeds\FeedManager;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Throwable;

class CheckFeedsCommand extends Command
{
    protected $signature = 'nova-rss:check
        {--source=* : Limit the check to these source keys}
        {--category=* : Limit the check to these category keys}';

    protected $description = 'Fetch every configured RSS source and report which ones are healthy';

    public function handle(SourceRepository $sources, FeedManager $feeds): int
    {
        $selected = $this->select($sources);

        if ($selected->isEmpty()) {
            $this->components->warn('No sources matched.');

            return self::SUCCESS;
        }

        $rows = [];
        $failed = 0;

        foreach ($selected as $source) {
            /** @var Source $source */
            $startedAt = microtime(true);

            try {
                $feed = $feeds->get($source, null, fresh: true);
                $rows[] = [
                    '<fg=green>ok</>',
                    $source->key,
                    $feed->parser ?? '-',
                    (string) count($feed->items),
                    $this->ms($startedAt),
                    $source->url,
                ];
            } catch (Throwable $e) {
                $failed++;
                $rows[] = [
                    '<fg=red>fail</>',
                    $source->key,
                    '-',
                    '0',
                    $this->ms($startedAt),
                    $e->getMessage(),
                ];
            }
        }

        $this->table(['Status', 'Source', 'Parser', 'Items', 'Time', 'URL / error'], $rows);

        if ($failed > 0) {
            $this->components->error("{$failed} of {$selected->count()} sources failed.");

            return self::FAILURE;
        }

        $this->components->info("All {$selected->count()} sources are healthy.");

        return self::SUCCESS;
    }

    /**
     * @return Collection<string, Source>
     */
    protected function select(SourceRepository $sources): Collection
    {
        /** @var array<int, string> $keys */
        $keys = (array) $this->option('source');
        /** @var array<int, string> $categories */
        $categories = (array) $this->option('category');

        if ($keys === [] && $categories === []) {
            return $sources->all();
        }

        $selected = $sources->only($keys);

        return $categories === [] ? $selected : $selected->merge($sources->inCategories($categories));
    }

    protected function ms(float $startedAt): string
    {
        return round((microtime(true) - $startedAt) * 1000).' ms';
    }
}
