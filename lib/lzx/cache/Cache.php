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

    public static function setHandler(CacheHandlerInterface $handler): void
    {
        self::$handler = $handler;
    }

    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    public function __construct(string $key)
    {
        $this->key = self::$handler->getCleanName($key);
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function store(string $data): void
    {
        $this->data = $data;
        $this->dirty = true;
    }

    public function delete(): void
    {
        // clear data
        $this->data = null;
        $this->dirty = true;
        $this->deleted = true;
    }

    public function addParent(string $key): void
    {
        $cleanKey = self::$handler->getCleanName($key);
        if ($cleanKey && !in_array($cleanKey, $this->parents)) {
            $this->parents[] = $cleanKey;
        }
        $this->dirty = true;
    }

    abstract public function flush(): void;

    protected function deleteDataFile(): void
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

    protected function writeDataFile(string $data): void
    {
        file_put_contents(self::$handler->getFileName($this), $data, LOCK_EX);
    }

    protected function deleteChildren(): void
    {
        foreach (self::$handler->getChildren($this->id) as $key) {
            $cache = self::$handler->createCache($key);
            $cache->delete();
            $cache->flush();
        }
    }
}
