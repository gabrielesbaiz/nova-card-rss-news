<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Console;

use Gabrielesbaiz\NovaCardRssNews\Sources\FeedDiscoverer;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DiscoverFeedCommand extends Command
{
    protected $signature = 'nova-rss:discover {url : A website URL, not necessarily the feed itself}';

    protected $description = 'Find the RSS/Atom/JSON feed behind a website URL';

    public function handle(FeedDiscoverer $discoverer): int
    {
        $url = (string) $this->argument('url');

        $this->components->info("Looking for feeds on {$url}…");

        $found = $discoverer->discover($url);

        if ($found === []) {
            $this->components->error('No feed found.');

            return self::FAILURE;
        }

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
        $key = (string) Str::of($first['title'] ?? $url)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_');

        $this->newLine();
        $this->line("'{$key}' => ['title' => '".addslashes((string) ($first['title'] ?? $key))."', 'url' => '{$first['url']}'],");

        return self::SUCCESS;
    }
}
