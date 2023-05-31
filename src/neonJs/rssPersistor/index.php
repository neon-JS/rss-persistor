<?php

declare(strict_types=1);

namespace neonjs\rsspersistor;

use \neonjs\rsspersistor\persistence\RssPersistenceService;
use \neonjs\rsspersistor\database\RssEntryStorage;
use \neonjs\rsspersistor\rss\RssStreamProvider;

spl_autoload_register(function (string $className) {
    $fileName = str_replace('\\', '/', $className) . '.php';
    include __DIR__ . '/../../' . $fileName;
});

$rssStreamProvider = new RssStreamProvider();
$rssEntryStorage = new RssEntryStorage();
$rssPersistenceService = new RssPersistenceService($rssStreamProvider, $rssEntryStorage);

$rssEntryStorage->initializeTables();
$rssPersistenceService->persistStream();

echo "Updated database" . PHP_EOL;