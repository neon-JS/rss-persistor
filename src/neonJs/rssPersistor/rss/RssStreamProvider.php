<?php

declare(strict_types=1);

namespace neonjs\rsspersistor\rss;

use Exception;
use SimpleXMLElement;

readonly class RssStreamProvider
{
    private const ENV_KEY_URL = 'url';

    public function provide(): SimpleXMLElement
    {
        $url = $_ENV[self::ENV_KEY_URL] ?? null ?: throw new Exception('No url configured');

        $content = file_get_contents($url);
        if (empty($content)) {
            throw new Exception('Could not load RSS feed');
        }

        /* prevent possible XXE attack */
        libxml_set_external_entity_loader(null);

        return simplexml_load_string($content) ?? throw new Exception('RSS feed is invalid');
    }
}
