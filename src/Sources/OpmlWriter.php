<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Sources;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Illuminate\Support\Collection;

/**
 * Writes the resolved catalogue back out as OPML 2.0.
 */
final class OpmlWriter
{
    /**
     * @param  Collection<array-key, Source>  $sources
     */
    public function write(Collection $sources, string $title = 'NovaCard RSS News'): string
    {
        $lines = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<opml version="2.0">',
            '  <head>',
            '    <title>'.$this->escape($title).'</title>',
            '  </head>',
            '  <body>',
        ];

        foreach ($sources->groupBy(static fn (Source $source): string => $source->categoryKey) as $group) {
            /** @var Collection<array-key, Source> $group */
            $lines[] = '    <outline text="'.$this->escape($group->first()->categoryLabel()).'">';

            foreach ($group as $source) {
                $lines[] = '      <outline type="rss" text="'.$this->escape($source->label()).'"'
                    .' title="'.$this->escape($source->label()).'"'
                    .' xmlUrl="'.$this->escape($source->url).'"'
                    .($source->site() !== null ? ' htmlUrl="'.$this->escape($source->site()).'"' : '')
                    .' />';
            }

            $lines[] = '    </outline>';
        }

        $lines[] = '  </body>';
        $lines[] = '</opml>';

        return implode("\n", $lines)."\n";
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
