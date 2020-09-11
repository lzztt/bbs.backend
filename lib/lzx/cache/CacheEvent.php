<?php declare(strict_types=1);

namespace lzx\cache;

use lzx\cache\Cache;

class CacheEvent extends Cache
{
    protected $children = [];

    public function __construct(string $key, int $objectID = 0)
    {
        parent::__construct($key);

        $this->data = (int) $objectID;
        if ($this->data < 0) {
            $this->data = 0;
        }
    }

    public function addListener(Cache $c): void
    {
        if ($c) {
            if (!in_array($c->getKey(), $this->children)) {
                $this->children[] = $c->getKey();
            }
            $this->dirty = true;
        }
    }

    public function trigger(): void
    {
        $this->deleted = true;
        $this->dirty = true;
    }

    public function flush(): void
    {
        if ($this->dirty) {
            $this->id = self::$handler->getId($this->key);

            if ($this->deleted) {
                // update children
                foreach (array_unique(array_merge($this->children, self::$handler->getEventListeners($this))) as $key) {
                    self::deleteCache($key);
                }

                // clear current children
                $this->children = [];
            } else {
                self::$handler->addEventListeners($this, $this->children);
            }
            $this->dirty = false;
        }
    }
}
