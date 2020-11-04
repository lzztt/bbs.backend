<?php

declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;
use lzx\cache\SegmentCache;

class PageCache extends Cache
{
    protected $segments = [];

    public function getSegment(string $key): SegmentCache
    {
        $key = $this->handler->cleanKey($key);

        if (!array_key_exists($key, $this->segments)) {
            $this->segments[$key] = new SegmentCache($key, $this->handler);
            $this->addParent($key);
        }

        return $this->segments[$key];
    }

    public function addChild(string $key): void
    {
        throw new Exception(self::NOT_SUPPORTED);
    }

    public function flush(): void
    {
        if ($this->dirty) {
            $children = $this->handler->fetchChildren($this);
            if ($this->deleted) {
                // delete, data first
                $this->handler->deleteDataFile($this);
                $this->handler->syncParents($this, []);
                $this->handler->syncChildren($this, []);
            } else {
                // save
                if ($this->data) {
                    // save (flush) all segments first.
                    // this will delete segment's children (may include this cache)
                    foreach ($this->segments as $seg) {
                        $seg->flush();
                    }

                    $this->handler->syncParents($this, $this->parents);
                    $this->handler->syncDataFile($this);
                }
            }

            // delete(flush) children
            foreach ($children as $key) {
                $this->handler->deleteCache($key);
            }

            $this->dirty = false;
        }
    }
}
