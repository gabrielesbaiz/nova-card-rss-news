<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Sources\FeedDiscoverer;
use Gabrielesbaiz\NovaCardRssNews\Sources\OpmlReader;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ImportOpmlCommand extends Command
{
    protected $signature = 'nova-rss:import
        {file : Path to the OPML file}
        {--category= : Force every imported feed into this category}
        {--validate : Fetch each feed and skip the ones that do not answer}';

    protected $description = 'Turn an OPML export from any feed reader into a config block';

    public function handle(OpmlReader $reader, FeedDiscoverer $discoverer): int
    {
        /** @var Collection<int, Source> $sources */
        $sources = $reader->readFile((string) $this->argument('file'));

        if ($sources->isEmpty()) {
            $this->components->warn('No feeds found in that OPML file.');

            return self::SUCCESS;
        }

        $category = $this->option('category');

        if ($category !== null) {
            $sources = $sources->map(fn (Source $source): Source => $source->with(categoryKey: (string) $category));
        }

        if ($this->option('validate')) {
            $sources = $sources->filter(function (Source $source) use ($discoverer): bool {
                $ok = $discoverer->validate($source->url) !== null;

                $this->components->twoColumnDetail($source->key, $ok ? '<fg=green>ok</>' : '<fg=red>skipped</>');

                return $ok;
            });
        }

        $this->line($this->render($sources));
        $this->newLine();
        $this->components->info("Imported {$sources->count()} feeds. Paste the block above into config/nova-card-rss-news.php under 'sources'.");

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, Source>  $sources
     */
    protected function render(Collection $sources): string
    {
        $lines = ['[', "    'categories' => ["];

        foreach ($sources->groupBy(static fn (Source $source): string => $source->categoryKey) as $categoryKey => $group) {
            /** @var Collection<int, Source> $group */
            $lines[] = "        '{$categoryKey}' => [";
            $lines[] = "            'label' => '".addslashes($group->first()->categoryLabel())."',";
            $lines[] = "            'sources' => [";

            foreach ($group as $source) {
                $lines[] = "                '{$source->key}' => ['title' => '".addslashes($source->label())."', 'url' => '{$source->url}'],";
            }

            $lines[] = '            ],';
            $lines[] = '        ],';
        }

        $lines[] = '    ],';
        $lines[] = '],';

        return implode("\n", $lines);
    }
}
