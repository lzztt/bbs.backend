<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;
use lzx\cache\CacheHandler;

class CacheEvent extends Cache
{

    public function __construct(string $key, int $objectID = 0)
    {
        $id = $objectID > 0 ? $objectID : 0;
        parent::__construct($key . ':' . $id, CacheHandler::getInstance());
    }

    public function addParent(string $key): void
    {
        throw new Exception('not supported');
    }

    public function addChild(string $key): void
    {
        throw new Exception('not supported');
    }

    public function addListener(Cache $cache): void
    {
        parent::addChild($cache->getKey());
    }

    public function trigger(): void
    {
        $this->delete();
    }

    public function flush(): void
    {
        if ($this->dirty) {
            if ($this->deleted) {
                // delete(flush) children
                foreach (array_unique(array_merge($this->children, $this->handler->fetchChildren($this))) as $key) {
                    $this->handler->deleteCache($key);
                }
            } else {
                // save, no data refresh
                $this->handler->addChildren($this, $this->children);
            }

            $this->dirty = false;
        }
    }
}
