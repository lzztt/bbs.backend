<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\Cache;

interface CacheHandlerInterface
{
    // Factory design patern interface
    public function createCache(string $name): Cache;

    public function getCleanName(string $name): string;

    public function getFileName(Cache $cache): string;

    public function getID(string $name): int;

    public function unlinkParents(int $id): void;

    public function linkParents(int $id, array $parents): void;

    public function getChildren(int $id): array;

    public function unlinkEvents(int $id): void;

    public function getEventListeners(int $eid, int $oid): array;

    public function addEventListeners(int $eid, int $oid, array $listeners): void;
}
