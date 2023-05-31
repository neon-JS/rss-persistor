<?php

declare(strict_types=1);

namespace neonjs\rsspersistor\persistence;

use \DateTimeImmutable;
use \DateTimeInterface;
use \neonjs\rsspersistor\database\RssEntryStorage;
use \neonjs\rsspersistor\database\RssEntry;
use \neonjs\rsspersistor\rss\RssStreamProvider;

readonly class RssPersistenceService
{
    private const VALID_WORD_PATTERN = '/([a-z0-9-öäüßÄÖÜẞ]+)/i';

    public function __construct(
        private RssStreamProvider $rssStreamProvider,
        private RssEntryStorage $rssEntryStorage,
    ) {
    }

    public function persistStream(): void
    {
        $streamData = $this->rssStreamProvider->provide();

        foreach ($streamData->channel->item as $child) {
            $title = (string)($child->title ?? null);
            $guid = (string)($child->guid ?? null);
            $link = (string)($child->link ?? null);
            $category = (string)($child->category ?? null);
            $pubDate = (string)($child->pubDate ?? null);
            $publicationDate = DateTimeImmutable::createFromFormat(DateTimeInterface::RFC7231, $pubDate) ?: null;

            if (empty($title) || empty($guid)) {
                continue;
            }

            $rssEntry = new RssEntry(
                $title,
                $guid,
                $link,
                $publicationDate,
                $category
            );

            $titleWords = $this->extractWords($rssEntry);

            $this->rssEntryStorage->store($rssEntry, $titleWords);
        }
    }

    private function extractWords(RssEntry $rssEntry): array
    {
        $words = [];
        foreach (explode(' ', $rssEntry->getTitle()) as $word) {
            if (preg_match(self::VALID_WORD_PATTERN, $word, $matches) === 1) {
                $words[] = $matches[1];
            }
        }

        return array_unique($words);
    }
}
