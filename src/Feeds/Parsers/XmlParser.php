<?php

declare(strict_types=1);

namespace Gabrielesbaiz\NovaCardRssNews\Feeds\Parsers;

use Gabrielesbaiz\NovaCardRssNews\Contracts\FeedParser;
use SimpleXMLElement;
use Throwable;

abstract class XmlParser implements FeedParser
{
    /**
     * Root element name this parser answers to.
     */
    abstract protected function rootElement(): string;

    public function supports(string $body): bool
    {
        $root = $this->root($body);

        return $root !== null && $root->getName() === $this->rootElement();
    }

    protected function root(string $body): ?SimpleXMLElement
    {
        $body = ltrim($body, "\xEF\xBB\xBF \t\n\r");

        if ($body === '' || $body[0] !== '<') {
            return null;
        }

        $previous = libxml_use_internal_errors(true);

        try {
            $xml = new SimpleXMLElement($body, LIBXML_NOCDATA | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET);
        } catch (Throwable) {
            return null;
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return $xml;
    }

    /**
     * Read a namespaced child (e.g. content:encoded, dc:creator) as a string.
     */
    protected function namespaced(SimpleXMLElement $node, string $namespace, string $tag): string
    {
        $children = $node->children($namespace);

        return isset($children->{$tag}) ? (string) $children->{$tag} : '';
    }
}
