<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;

class SegmentCache extends Cache
{

    public function fetch(): string
    {
        if (!$this->data) {
            $this->data = $this->handler->fetchData($this);
        }
        return $this->data;
    }

    public function addChild(string $key): void
    {
        throw new Exception('not supported');
    }

    public function flush(): void
    {
        if ($this->dirty) {
            $children = $this->handler->fetchChildren($this);
            if ($this->deleted) {
                // delete, data first
                $this->handler->deleteData($this);
                $this->handler->syncParents($this, []);
                $this->handler->syncChildren($this, []);
            } else {
                // save
                if ($this->data) {
                    // link to current parent nodes
                    $this->handler->syncParents($this, $this->parents);
                    // save data
                    $this->handler->syncData($this);
                }
            }

            // delete(flush) child cache nodes
            foreach ($children as $key) {
                $this->handler->deleteCache($key);
            }

            $this->dirty = false;
        }
    }
}
