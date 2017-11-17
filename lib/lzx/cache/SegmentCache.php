<?php declare(strict_types=1);

namespace lzx\cache;

use Exception;
use lzx\cache\Cache;

class SegmentCache extends Cache
{
    /**
     * fetch segment data from cache
     * @return boolean
     */
    public function fetch()
    {
        if ($this->data) {
            return $this->data;
        }

        return $this->fetchFromFile();
    }

    public function flush()
    {
        if ($this->dirty) {
            $this->id = self::$handler->getID($this->key);

            // unlink existing parent cache nodes
            self::$handler->unlinkParents($this->id);
            self::$handler->unlinkEvents($this->id);

            // update self
            if ($this->deleted) {
                // delete self
                $this->deleteDataFile();
            } else {
                if ($this->data) {
                    // save self
                    $this->writeDataFile($this->data);

                    // link to current parent nodes
                    self::$handler->linkParents($this->id, $this->parents);
                }
            }

            // delete(flush) child cache nodes
            $this->deleteChildren();

            $this->dirty = false;
        }
    }

    public function fetchFromFile()
    {
        $file = self::$handler->getFileName($this);
        try {
            // read only if exist!!
            return is_file($file) ? file_get_contents($file) : null;
        } catch (Exception $e) {
            if (self::$logger) {
                self::$logger->warn('Could not read from file [' . $file . ']: ' . $e->getMessage());
            } else {
                error_log('Could not read from file [' . $file . ']: ' . $e->getMessage());
            }
            return null;
        }
    }
}
