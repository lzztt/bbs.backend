<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;
use lzx\html\Template;

class SegmentCache extends Cache
{

    public function getData(): ?Template
    {
        if (!$this->data) {
            $data = $this->handler->fetchData($this);
            if ($data) {
                $this->data = Template::fromStr($data);
            }
        }
        return $this->data;
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
                $this->handler->deleteData($this);
                $this->handler->syncParents($this, []);
                $this->handler->syncChildren($this, []);
            } else {
                // save
                if ($this->data) {
                    $this->handler->syncParents($this, $this->parents);
                    $this->handler->syncData($this);
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
