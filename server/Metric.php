<?php

declare(strict_types=1);

namespace site;

use lzx\db\MemStore;
use Prometheus\CollectorRegistry;
use Prometheus\Storage\Redis;

class Metric extends CollectorRegistry
{
    public function __construct()
    {
        $metricStore = Redis::fromExistingConnection(MemStore::getRedis(MemStore::METRICS));
        $metricStore->setPrefix('');
        parent::__construct($metricStore, false);
    }
}
