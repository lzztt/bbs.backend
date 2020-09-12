<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use Redis;
use lzx\cache\Cache;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;
use lzx\core\Logger;

class CacheHandler
{
    protected const SEP = ':';
    protected Logger $logger;
    protected string $path;
    protected string $domain;
    protected Redis $db;

    private function __construct()
    {
        $this->db = new Redis();
        $this->db->pconnect('/run/redis/redis-server.sock');
    }

    /**
     * singleton design pattern
     */
    public static function getInstance(): self
    {
        static $instance;

        if (!$instance) {
            $instance = new self();
        }

        return $instance;
    }

    public function setLogger(Logger $logger): void
    {
        $this->logger = $logger;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function setDomain(string $domain): void
    {
        $this->domain = str_replace('.com', '', $domain);
    }

    /**
     * Factory design patern
     */
    public function createCache(string $key): Cache
    {
        $key = $this->cleanKey($key);
        return $key[1] === '/'
            ? new PageCache($key, $this)
            : new SegmentCache($key, $this);
    }

    public function deleteCache(string $key): void
    {
        $cache = $this->createCache($key);
        $cache->delete();
        $cache->flush();
    }

    public function cleanKey(string $key): string
    {
        if (!$key || $key === self::SEP) {
            throw new Exception('cache key is empty');
        }

        // already processed
        if ($key[0] === self::SEP) {
            return $key;
        }

        if (strpos($key, ' ') !== false) {
            throw new Exception('cache key contains spaces: ' . $key);
        }

        if ($key[0] === '/') {
            // page uri
            if (strpos($key, '#') === false) {
                switch (substr_count($key, '?')) {
                    case 0:
                        $key = $key . '#';
                        break;
                    case 1:
                        // has query string
                        $key = str_replace('?', '#', $key);
                        break;
                    default:
                        throw new Exception('page uri has multiple "?" charactors: ' . $key);
                }
            } else {
                throw new Exception('page uri has "#" charactor: ' . $key);
            }
        } else {
            // segment key or event key
            $key = preg_replace('/[^0-9a-z\.\_\-]/i', '_', $key);
        }

        return self::SEP . $key;
    }

    protected function getFileName(Cache $cache): string
    {
        if (get_class($cache) !== 'lzx\cache\PageCache') {
            throw new Exception('unsupport cache type: ' . get_class($cache));
        }

        $key = substr($cache->getKey(), 1);
        return $this->path . '/page' . $key . '.html.gz';
    }

    public function deleteDataFile(Cache $cache): void
    {
        try {
            unlink($this->getFileName($cache));
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->warning($e->getMessage());
            } else {
                error_log($e->getMessage());
            }
        }
    }

    public function syncDataFile(Cache $cache): void
    {
        $filename = $this->getFileName($cache);
        $dir = dirname($filename);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        // gzip data for public cache file used by webserver
        // use 6 as default and equal to webserver gzip compression level
        file_put_contents($filename, gzencode($cache->getData(), 6), LOCK_EX);
    }

    protected function getDataKey($key): string
    {
        return $this->domain . $key;
    }

    protected function getParentsKey($key): string
    {
        return $this->domain . $key . ':p';
    }

    protected function getChildrenKey($key): string
    {
        return $this->domain . $key . ':c';
    }

    public function fetchData(Cache $cache): string
    {
        $data = $this->db->get($this->getDataKey($cache->getKey()));
        return $data ? $data : '';
    }

    public function syncData(Cache $cache): void
    {
        $this->db->set($this->getDataKey($cache->getKey()), $cache->getData());
    }

    public function deleteData(Cache $cache): void
    {
        $this->db->del($this->getDataKey($cache->getKey()));
    }

    public function fetchParents(Cache $cache): array
    {
        return $this->db->sMembers($this->getParentsKey($cache->getKey()));
    }

    public function syncParents(Cache $cache, array $parents): void
    {
        $existing = $this->fetchParents($cache);
        $old = array_diff($existing, $parents);
        $new = array_diff($parents, $existing);

        if ($old) {
            foreach ($old as $key) {
                $this->db->sRem($this->getChildrenKey($key), $cache->getKey());
            }

            if (count($old) === count($existing)) {
                $this->db->del($this->getParentsKey($cache->getKey()));
            } else {
                $this->db->sRem($this->getParentsKey($cache->getKey()), ...$old);
            }
        }

        if ($new) {
            foreach ($new as $key) {
                $this->db->sAdd($this->getChildrenKey($key), $cache->getKey());
            }
            $this->db->sAdd($this->getParentsKey($cache->getKey()), ...$new);
        }
    }

    public function fetchChildren(Cache $cache): array
    {
        return $this->db->sMembers($this->getChildrenKey($cache->getKey()));
    }

    public function syncChildren(Cache $cache, array $children): void
    {
        $existing = $this->fetchChildren($cache);
        $old = array_diff($existing, $children);
        $new = array_diff($children, $existing);

        if ($old) {
            foreach ($old as $key) {
                $this->db->sRem($this->getParentsKey($key), $cache->getKey());
            }
            if (count($old) === count($existing)) {
                $this->db->del($this->getChildrenKey($cache->getKey()));
            } else {
                $this->db->sRem($this->getChildrenKey($cache->getKey()), ...$old);
            }
        }

        if ($new) {
            foreach ($new as $key) {
                $this->db->sAdd($this->getParentsKey($key), $cache->getKey());
            }
            $this->db->sAdd($this->getChildrenKey($cache->getKey()), ...$new);
        }
    }
}
