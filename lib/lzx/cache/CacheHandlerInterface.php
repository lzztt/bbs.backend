<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\Cache;

interface CacheHandlerInterface
{
    // Factory design patern interface
    public function createCache(string $name): Cache;

    public function getCleanName(string $name): string;

    public function getFileName(Cache $cache): string;

    public function getId(string $name): int;

    public function unlinkParents(Cache $cache): void;

    public function linkParents(Cache $cache, array $parents): void;

    public function getChildren(Cache $cache): array;

    public function unlinkEvents(Cache $cache): void;

    public function getEventListeners(Cache $cache): array;

    public function addEventListeners(Cache $cache, array $listeners): void;
}
