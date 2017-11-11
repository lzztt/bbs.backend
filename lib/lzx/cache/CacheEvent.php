<?php

namespace lzx\cache;

use lzx\cache\Cache;
use lzx\cache\CacheHandlerInterface;

/**
 * @property Cache[] $listeners
 * @property CacheHandler $handler
 */
class CacheEvent
{
    static protected $handler;
    protected $id;
    protected $name;
    protected $objID;
    protected $listeners = [];
    protected $dirty = false;
    protected $triggered = false;

    public static function setHandler(CacheHandlerInterface $handler)
    {
        self::$handler = $handler;
    }

    public function __construct($name, $objectID = 0)
    {
        $this->name = self::$handler->getCleanName($name);

        $this->objID = (int) $objectID;
        if ($this->objID < 0) {
            $this->objID = 0;
        }
    }

    public function getName()
    {
        return $this->objID ? $this->name . '<' . $this->objID . '>' : $this->name;
    }

    /**
     *
     * add a listener to an event
     */
    public function addListener(Cache $c)
    {
        if ($c) {
            if (!in_array($c->getKey(), $this->listeners)) {
                $this->listeners[] = $c->getKey();
            }
            $this->dirty = true;
        }
    }

    /**
     * trigger an event.
     * This will delete and unlink all its listeners
     */
    public function trigger()
    {
        $this->triggered = true;
        $this->dirty = true;
    }

    public function flush()
    {
        if ($this->dirty) {
            $this->id = self::$handler->getID($this->name);

            if ($this->triggered) {
                // update current listeners
                foreach ($this->listeners as $key) {
                    $c = self::$handler->createCache($key);
                    $c->delete();
                    $c->flush();
                }
                // clear current listeners
                $this->listeners = [];

                // update listeners in DB
                foreach (self::$handler->getEventListeners($this->id, $this->objID) as $key) {
                    $c = self::$handler->createCache($key);
                    $c->delete();
                    $c->flush();
                }
            } else {
                self::$handler->addEventListeners($this->id, $this->objID, $this->listeners);
            }
            $this->dirty = false;
        }
    }
}
