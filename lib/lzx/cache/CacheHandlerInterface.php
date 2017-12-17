<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\Cache;

interface CacheHandlerInterface
{
    // Factory design patern interface
    public function createCache($name): Cache;

    public function getCleanName($name): string;

    public function getFileName(Cache $cache): string;

    public function getID($name): int;

    public function unlinkParents($id): void;

    public function linkParents($id, array $parents): void;

    public function getChildren($id): array;

    public function unlinkEvents($id): void;

    public function getEventListeners($eid, $oid): array;

    public function addEventListeners($eid, $oid, array $listeners): void;
}
