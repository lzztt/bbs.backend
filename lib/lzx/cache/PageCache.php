<?php

namespace lzx\cache;

use lzx\cache\Cache;
use lzx\cache\SegmentCache;

/**
 * @property \site\SegmentCache[] $segments segments for page cache
 */
class PageCache extends Cache
{
    protected $segments = [];

    /**
     *
     * @return SegmentCache
     */
    public function getSegment($key)
    {
        $cleanKey = self::$handler->getCleanName($key);

        if (!array_key_exists($cleanKey, $this->segments)) {
            $this->segments[$cleanKey] = new SegmentCache($key);
        }

        return $this->segments[$cleanKey];
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
                    // save (flush) all segments first, this may delete segment's children (this cache)
                    foreach ($this->segments as $seg) {
                        $seg->flush();
                    }

                    // save self
                    // gzip data for public cache file used by webserver
                    // use 6 as default and equal to webserver gzip compression level
                    $this->writeDataFile(gzencode($this->data, 6));

                    // make segments as parent nodes
                    foreach (array_keys($this->segments) as $pkey) {
                        $this->parents[] = $pkey;
                    }

                    // link to current parent nodes
                    self::$handler->linkParents($this->id, $this->parents);
                }
            }

            // flush/delete child cache nodes
            $this->deleteChildren();

            $this->dirty = false;
        }
    }
}
