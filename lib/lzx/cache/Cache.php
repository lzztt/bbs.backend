<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\CacheHandlerInterface;
use lzx\core\Logger;

abstract class Cache
{
    static protected CacheHandlerInterface $handler;
    static protected Logger $logger;
    protected string $key;
    protected $data;
    protected bool $deleted = false;
    protected array $parents = [];
    protected int $id;
    protected bool $dirty = false;

    public static function setHandler(CacheHandlerInterface $handler): void
    {
        self::$handler = $handler;
    }

    public static function setLogger(Logger $logger): void
    {
        self::$logger = $logger;
    }

    protected static function deleteCache($key): void
    {
        $cache = self::$handler->createCache($key);
        $cache->delete();
        $cache->flush();
    }

    public function __construct(string $key)
    {
        $this->key = self::$handler->getCleanName($key);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getData()
    {
        return $this->data;
    }

    public function store(string $data): void
    {
        $this->data = $data;
        $this->dirty = true;
    }

    public function delete(): void
    {
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
                self::$logger->warning($e->getMessage());
            } else {
                error_log($e->getMessage());
            }
        }
    }

    protected function writeDataFile(string $data): void
    {
        file_put_contents(self::$handler->getFileName($this), $data, LOCK_EX);
    }
}
