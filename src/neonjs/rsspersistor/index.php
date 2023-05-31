<?php

declare(strict_types=1);

namespace neonjs\rsspersistor;

require_once __DIR__ . '/database/RssEntry.php';
require_once __DIR__ . '/database/RssEntryStorage.php';
require_once __DIR__ . '/persistence/RssPersistenceService.php';
require_once __DIR__ . '/rss/RssStreamProvider.php';

use \neonjs\rsspersistor\persistence\RssPersistenceService;
use \neonjs\rsspersistor\database\RssEntryStorage;
use \neonjs\rsspersistor\rss\RssStreamProvider;

$rssStreamProvider = new RssStreamProvider();
$rssEntryStorage = new RssEntryStorage();
$rssPersistenceService = new RssPersistenceService($rssStreamProvider, $rssEntryStorage);

$rssEntryStorage->initializeTables();
$rssPersistenceService->persistStream();

echo "Updated database" . PHP_EOL;
