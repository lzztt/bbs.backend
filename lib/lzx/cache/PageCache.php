<?php declare(strict_types=1);

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
        throw new Exception('not supported');
    }

    public function flush(): void
    {
        if ($this->dirty) {
            if ($this->deleted) {
                // delete self, data first
                $this->handler->deleteDataFile($this);
                $this->handler->syncParents($this, []);
            } else {
                if ($this->data) {
                    // save (flush) all segments first, this may delete segment's children (this cache)
                    foreach ($this->segments as $seg) {
                        $seg->flush();
                    }

                    // link to current parent nodes
                    $this->handler->syncParents($this, $this->parents);

                    // save data
                    $this->handler->syncDataFile($this);
                }
            }

            $this->dirty = false;
        }
    }
}
