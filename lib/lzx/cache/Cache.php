<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\CacheHandlerInterface;
use lzx\core\Logger;

abstract class Cache
{
    static protected $handler;
    static protected $logger;
    static protected $ids = [];
    protected $key;
    protected $data;
    protected $deleted = false;
    protected $parents = [];
    protected $events = [];
    protected $id;
    protected $dirty = false;

    public static function setHandler(CacheHandlerInterface $handler)
    {
        self::$handler = $handler;
    }

    public static function setLogger(Logger $logger)
    {
        self::$logger = $logger;
    }

    public function __construct($key)
    {
        $this->key = self::$handler->getCleanName($key);
    }

    public function getKey()
    {
        return $this->key;
    }

    public function fetch()
    {
        return $this->data;
    }

    public function store($data)
    {
        $this->data = (string) $data;
        $this->dirty = true;
    }

    public function delete()
    {
        // clear data
        $this->data = null;
        $this->dirty = true;
        $this->deleted = true;
    }

    public function addParent($key)
    {
        $cleanKey = self::$handler->getCleanName($key);
        if ($cleanKey && !in_array($cleanKey, $this->parents)) {
            $this->parents[] = $cleanKey;
        }
        $this->dirty = true;
    }

    abstract public function flush();

    protected function deleteDataFile()
    {
        try {
            unlink(self::$handler->getFileName($this));
        } catch (Exception $e) {
            if (self::$logger) {
                self::$logger->warn($e->getMessage());
            } else {
                error_log($e->getMessage());
            }
        }
    }

    protected function writeDataFile($data)
    {
        file_put_contents(self::$handler->getFileName($this), $data, LOCK_EX);
    }

    protected function deleteChildren()
    {
        foreach (self::$handler->getChildren($this->id) as $key) {
            $cache = self::$handler->createCache($key);
            $cache->delete();
            $cache->flush();
        }
    }
}
