<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Sources\FeedDiscoverer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DiscoverFeedCommand extends Command
{
    protected $signature = 'nova-rss:discover
        {url : A website URL, not necessarily the feed itself}
        {--limit=25 : How many candidate feeds to fetch and verify}';

    protected $description = 'Find the RSS/Atom/JSON feed behind a website URL';

    public function handle(FeedDiscoverer $discoverer): int
    {
        $url = (string) $this->argument('url');

        $this->components->info("Looking for feeds on {$url}…");

        $found = $discoverer->discover($url, max(1, (int) $this->option('limit')));

        if ($found === []) {
            $this->components->error($discoverer->skipped() > 0
                ? 'No feed among the candidates checked. '.$discoverer->skipped().' more were not checked — raise --limit.'
                : 'No feed found.');

            return self::FAILURE;
        }

        if ($discoverer->skipped() > 0) {
            $this->components->warn(
                $discoverer->skipped().' further candidates were not checked. Raise --limit to include them.'
            );
        }

        $this->components->info(count($found).' feed(s) verified.');

        $this->table(
            ['Feed URL', 'Title', 'Format', 'Items'],
            array_map(static fn (array $feed): array => [
                $feed['url'],
                $feed['title'] ?? '-',
                $feed['parser'],
                (string) $feed['items'],
            ], $found),
        );

        $first = $found[0];

        // Publishers put whole sentences in <title>; a config key wants a handle.
        $key = (string) Str::of($first['title'] ?? $url)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', '_')
            ->trim('_')
            ->explode('_')
            ->take(4)
            ->implode('_');

        $this->newLine();
        $this->line("'{$key}' => ['title' => '".addslashes((string) ($first['title'] ?? $key))."', 'url' => '{$first['url']}'],");

        return self::SUCCESS;
    }
}
