<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Sources\SourceRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ListFeedsCommand extends Command
{
    protected $signature = 'nova-rss:list
        {--category=* : Only these category keys}
        {--search= : Only sources whose key, title or category matches}
        {--keys : Print bare source keys, one per line, for copying}
        {--calls : Print ready-to-paste card calls, one per line}
        {--method=defaultSource : The call --calls writes: defaultSource or source}
        {--urls : Add the feed URL to the table}
        {--json : Print the catalogue as JSON}';

    protected $description = 'List the source keys available to source() and defaultSource()';

    public function handle(SourceRepository $sources): int
    {
        $selected = $this->select($sources);

        if ($selected->isEmpty()) {
            $this->components->warn('No sources matched. Check the presets enabled in config/nova-card-rss-news.php.');

            return self::FAILURE;
        }

        if ($this->option('keys')) {
            $selected->each(fn (Source $source) => $this->line($source->key));

            return self::SUCCESS;
        }

        if ($this->option('calls')) {
            $method = $this->option('method') === 'source' ? 'source' : 'defaultSource';
            $width = $selected->max(fn (Source $source): int => strlen($source->key)) + 2;

            $selected->each(function (Source $source) use ($method, $width): void {
                $call = "->{$method}('{$source->key}')";
                $this->line(str_pad($call, $width + strlen($method) + 6).'// '.$source->label());
            });

            return self::SUCCESS;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($selected->values(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $headers = ['Key', 'Title', 'Category'];

        if ($this->option('urls')) {
            $headers[] = 'URL';
        }

        $this->table($headers, $selected->map(function (Source $source): array {
            $row = [$source->key, $source->label(), $source->categoryLabel()];

            if ($this->option('urls')) {
                $row[] = $source->url;
            }

            return $row;
        })->all());

        $this->components->info(
            $selected->count().' source(s) in '.$selected->groupBy(fn (Source $s): string => $s->categoryKey)->count().' categories.'
        );
        $this->components->twoColumnDetail('Keys only', 'php artisan nova-rss:list --keys');
        $this->components->twoColumnDetail('Paste-ready calls', 'php artisan nova-rss:list --calls');

        $first = $selected->first();

        $this->newLine();
        $this->line("    <fg=gray>RssNewsSelectCard::make()</><fg=cyan>->defaultSource('{$first->key}')</><fg=gray>,</>");
        $this->line("    <fg=gray>RssNewsCard::make()</><fg=cyan>->source('{$first->key}')</><fg=gray>,</>");

        return self::SUCCESS;
    }

    /**
     * @return Collection<string, Source>
     */
    protected function select(SourceRepository $sources): Collection
    {
        /** @var array<int, string> $categories */
        $categories = (array) $this->option('category');

        $selected = $categories === [] ? $sources->all() : $sources->inCategories($categories);

        $search = trim((string) $this->option('search'));

        if ($search !== '') {
            $needle = mb_strtolower($search);

            $selected = $selected->filter(fn (Source $source): bool => str_contains(mb_strtolower(
                $source->key.' '.$source->label().' '.$source->categoryLabel()
            ), $needle));
        }

        // Grouped by category, alphabetical within it — the order the picker uses.
        return $selected
            ->sortBy([
                fn (Source $a, Source $b): int => strcmp($a->categoryLabel(), $b->categoryLabel()),
                fn (Source $a, Source $b): int => strcmp($a->label(), $b->label()),
            ]);
    }
}
