<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;

class SegmentCache extends Cache
{

    public function fetch(): string
    {
        if ($this->data) {
            return $this->data;
        }

        return $this->fetchFromFile();
    }

    public function flush(): void
    {
        if ($this->dirty) {
            $this->id = self::$handler->getId($this->key);

            // unlink existing parent cache nodes
            self::$handler->unlinkParents($this);
            self::$handler->unlinkEvents($this);

            // update self
            if ($this->deleted) {
                // delete self
                $this->deleteDataFile();
            } else {
                if ($this->data) {
                    // save self
                    $this->writeDataFile($this->data);

                    // link to current parent nodes
                    self::$handler->linkParents($this, $this->parents);
                }
            }

            // delete(flush) child cache nodes
            foreach (self::$handler->getChildren($this) as $key) {
                self::deleteCache($key);
            }

            $this->dirty = false;
        }
    }

    public function fetchFromFile(): string
    {
        $file = self::$handler->getFileName($this);
        try {
            // read only if exist!!
            return is_file($file) ? file_get_contents($file) : '';
        } catch (Exception $e) {
            if (self::$logger) {
                self::$logger->warning('Could not read from file [' . $file . ']: ' . $e->getMessage());
            } else {
                error_log('Could not read from file [' . $file . ']: ' . $e->getMessage());
            }
            return '';
        }
    }
}
