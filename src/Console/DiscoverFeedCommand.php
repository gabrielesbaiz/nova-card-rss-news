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

        $this->newLine();
        $this->line($this->configBlock($url, $found));
        $this->newLine();
        $this->components->info('Paste the block above into the \'sources\' array of config/nova-card-rss-news.php.');

        return self::SUCCESS;
    }

    /**
     * A pastable config block for everything discovered, not just the first
     * feed: an index page yields dozens, and one line is the least useful
     * part of the answer.
     *
     * @param  array<int, array{url: string, title: ?string, parser: string, items: int}>  $found
     */
    protected function configBlock(string $url, array $found): string
    {
        $category = (string) Str::of((string) parse_url($url, PHP_URL_HOST))
            ->replaceMatches('/^(www|feeds?|rss|xml\\d*)\\./', '')
            ->before('.')
            ->slug('_');

        $titles = array_map(static fn (array $feed): string => (string) ($feed['title'] ?? ''), $found);

        $lines = [
            '[',
            "    'categories' => [",
            "        '{$category}' => [",
            "            'label' => '".addslashes(Str::headline($category))."',",
            "            'sources' => [",
        ];

        $used = [];

        foreach ($found as $feed) {
            $label = $this->labelFor($feed, $titles);
            $key = $this->uniqueKey($label, $used);
            $used[] = $key;

            $lines[] = "                '{$key}' => ['title' => '".addslashes($label)."', 'url' => '{$feed['url']}'],";
        }

        return implode("\n", array_merge($lines, ['            ],', '        ],', '    ],', '],']));
    }

    /**
     * Feeds on one site often share a generic <title> — fourteen of them
     * called "corriere.it" is useless in a picker — so fall back to the
     * URL's own name when the title does not tell them apart.
     *
     * @param  array{url: string, title: ?string, parser: string, items: int}  $feed
     * @param  array<int, string>  $titles
     */
    protected function labelFor(array $feed, array $titles): string
    {
        $title = trim((string) ($feed['title'] ?? ''));
        $distinctive = $title !== '' && count(array_keys($titles, $title, true)) === 1;

        if ($distinctive) {
            return $this->shorten($title);
        }

        $segment = (string) Str::of((string) parse_url($feed['url'], PHP_URL_PATH))
            ->afterLast('/')
            ->before('.')
            ->replaceMatches('/[_-]+/', ' ')
            ->trim();

        return $segment === '' ? ($title ?: $feed['url']) : Str::headline($segment);
    }

    /**
     * A feed title is often "Site: a whole sentence about the site". Keep the
     * part before the colon or dash rather than chopping mid-word.
     */
    protected function shorten(string $title): string
    {
        if (mb_strlen($title) <= 60) {
            return $title;
        }

        foreach ([':', ' - ', ' – ', ' | '] as $separator) {
            $head = trim((string) Str::before($title, $separator));

            if ($head !== '' && $head !== trim($title) && mb_strlen($head) >= 4) {
                return $head;
            }
        }

        return rtrim(Str::words($title, 6, ''), ' ,.;:-');
    }

    /**
     * @param  array<int, string>  $used
     */
    protected function uniqueKey(string $label, array $used): string
    {
        $base = (string) Str::of($label)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_');
        $base = (string) Str::of($base)->explode('_')->take(4)->implode('_');
        $base = $base === '' ? 'feed' : $base;

        $key = $base;
        $suffix = 2;

        while (in_array($key, $used, true)) {
            $key = $base.'_'.$suffix++;
        }

        return $key;
    }
}
