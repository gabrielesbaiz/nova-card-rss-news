<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds;

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedParser;
use Gabrielesbaiz\NovaCardRssNews\Exceptions\UnsupportedFeedFormat;

/**
 * Sniffs the payload and hands it to the first parser that recognises it.
 */
final class ParserRegistry
{
    /**
     * @param  array<int, FeedParser>  $parsers
     */
    public function __construct(private array $parsers = []) {}

    public function register(FeedParser $parser): self
    {
        $this->parsers[] = $parser;

        return $this;
    }

    /**
     * @return array<int, FeedParser>
     */
    public function all(): array
    {
        return $this->parsers;
    }

    public function get(string $key): ?FeedParser
    {
        foreach ($this->parsers as $parser) {
            if ($parser->key() === $key) {
                return $parser;
            }
        }

        return null;
    }

    /**
     * @throws UnsupportedFeedFormat
     */
    public function resolve(string $body, ?string $forced = null, string $url = ''): FeedParser
    {
        if ($forced !== null) {
            $parser = $this->get($forced);

            if ($parser !== null) {
                return $parser;
            }
        }

        foreach ($this->parsers as $parser) {
            if ($parser->supports($body)) {
                return $parser;
            }
        }

        throw UnsupportedFeedFormat::for($url);
    }
}
