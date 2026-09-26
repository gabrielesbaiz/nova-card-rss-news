<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Sources\OpmlWriter;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Console\Command;

class ExportOpmlCommand extends Command
{
    protected $signature = 'nova-rss:export {--output= : Write to this file instead of stdout}';

    protected $description = 'Export the resolved feed catalogue as OPML';

    public function handle(SourceRepository $sources, OpmlWriter $writer): int
    {
        $opml = $writer->write($sources->all()->values());

        $output = $this->option('output');

        if ($output === null) {
            $this->line($opml);

            return self::SUCCESS;
        }

        file_put_contents((string) $output, $opml);
        $this->components->info("Exported {$sources->all()->count()} sources to {$output}.");

        return self::SUCCESS;
    }
}
