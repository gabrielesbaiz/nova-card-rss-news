<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Sources;

use Gabrielesbaiz\NovaCardRssNews\Data\Source;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\FeedException;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use SimpleXMLElement;
use Throwable;

/**
 * Reads the OPML files every feed reader exports. Nested outlines become
 * categories; leaf outlines with an xmlUrl become sources.
 */
final class OpmlReader
{
    /**
     * @return Collection<int, Source>
     */
    public function read(string $opml): Collection
    {
        $previous = libxml_use_internal_errors(true);

        try {
            $xml = new SimpleXMLElement($opml, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        } catch (Throwable $e) {
            throw new FeedException('The OPML document could not be parsed: '.$e->getMessage(), 0, $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($xml->getName() !== 'opml' || ! isset($xml->body)) {
            throw new FeedException('That document is not an OPML feed list.');
        }

        /** @var Collection<int, Source> $sources */
        $sources = new Collection;

        foreach ($xml->body->outline as $outline) {
            $this->walk($outline, 'general', null, $sources);
        }

        return $sources;
    }

    public function readFile(string $path): Collection
    {
        if (! is_readable($path)) {
            throw new FeedException("OPML file [{$path}] is not readable.");
        }

        return $this->read((string) file_get_contents($path));
    }

    /**
     * @param  Collection<int, Source>  $sources
     */
    private function walk(SimpleXMLElement $outline, string $categoryKey, ?string $categoryLabel, Collection $sources): void
    {
        $url = trim((string) ($outline['xmlUrl'] ?? ''));
        $text = trim((string) ($outline['title'] ?? $outline['text'] ?? ''));

        if ($url !== '') {
            $key = (string) Str::of($text !== '' ? $text : (parse_url($url, PHP_URL_HOST) ?: $url))
                ->lower()
                ->ascii()
                ->replaceMatches('/[^a-z0-9]+/', '_')
                ->trim('_');

            $sources->push(new Source(
                key: $key !== '' ? $key : 'feed_'.substr(sha1($url), 0, 8),
                title: $text !== '' ? $text : $url,
                url: $url,
                categoryKey: $categoryKey,
                categoryLabel: $categoryLabel,
                siteUrl: trim((string) ($outline['htmlUrl'] ?? '')) ?: null,
            ));

            return;
        }

        // A folder: its label becomes the category for everything beneath it.
        $childKey = $text !== ''
            ? (string) Str::of($text)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', '_')->trim('_')
            : $categoryKey;

        foreach ($outline->outline as $child) {
            $this->walk($child, $childKey !== '' ? $childKey : $categoryKey, $text !== '' ? $text : $categoryLabel, $sources);
        }
    }
}
