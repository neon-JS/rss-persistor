<?php

declare(strict_types=1);

namespace neonJs\rssPersistor\rss;

use SimpleXMLElement;

interface IStreamProvider
{
    public function provide(): SimpleXMLElement;
}
