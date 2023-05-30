<?php

declare(strict_types=1);

namespace neonJs\rssPersistor\database;

use \DateTimeImmutable;

readonly class RssEntry
{
    public function __construct(
        private readonly string $title,
        private readonly string $guid,
        private readonly ?string $link,
        private readonly ?DateTimeImmutable $publicationDate,
        private readonly ?string $category,
    ) {
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getGuid(): string
    {
        return $this->guid;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function getPublicationDate(): ?DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
