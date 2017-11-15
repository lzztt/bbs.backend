<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\Cache;

interface CacheHandlerInterface
{
    /**
     * Factory design patern
     * @return \lzx\cache\Cache
     */
    public function createCache($name);

    public function getCleanName($name);

    public function getFileName(Cache $cache);

    public function getID($name);

    public function unlinkParents($id);

    public function linkParents($id, array $parents);

    public function getChildren($id);

    public function unlinkEvents($id);

    public function getEventListeners($eid, $oid);

    public function addEventListeners($eid, $oid, array $listeners);
}
