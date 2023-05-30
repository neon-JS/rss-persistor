<?php

declare(strict_types=1);

namespace neonJs\rssPersistor;

use \neonJs\rssPersistor\persistence\RssPersistenceService;
use \neonJs\rssPersistor\database\RssEntryMapper;
use neonJs\rssPersistor\rss\StreamProvider;

spl_autoload_register(function (string $className) {
    $fileName = str_replace('\\', '/', $className) . '.php';
    include __DIR__ . '/../../' . $fileName;
});

$streamProvider = new StreamProvider();
$rssEntryMapper = new RssEntryMapper();
$rssPersistenceService = new RssPersistenceService($streamProvider, $rssEntryMapper);

$rssEntryMapper->initializeTables();
$rssPersistenceService->persistStream();

echo "Updated database" . PHP_EOL;