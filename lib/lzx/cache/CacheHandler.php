<?php

namespace lzx\cache;

use lzx\cache\CacheHandlerInterface;
use lzx\db\DB;
use lzx\cache\Cache;
use lzx\cache\PageCache;
use lzx\cache\SegmentCache;

class CacheHandler implements CacheHandlerInterface
{
    static public $path;
    protected $_db;
    // Cache tables
    protected $_tName;
    protected $_tTree;
    protected $_tEvent;

    private function __construct(DB $db)
    {
        $this->_db = $db;
        $this->_tName = 'cache_names';
        $this->_tTree = 'cache_tree';
        $this->_tEvent = 'cache_event_listeners';
    }

    /**
     * singleton design pattern
     *
     * @staticvar self $instance
     * @return \lzx\cache\CacheHandler
     */
    public static function getInstance(DB $db = null)
    {
        static $instance;

        if (!isset($instance)) {
            if ($db) {
                $instance = new self($db);
            } else {
                throw new \Exception('no instance is available. a DB object is required for creating a new instance.');
            }
        }
        return $instance;
    }

    public function getCacheTreeTable()
    {
        return $this->_tTree;
    }

    public function getCacheEventTable()
    {
        return $this->_tEvent;
    }

    public function setCacheTreeTable($treeTable)
    {
        $this->_tTree = $treeTable;
    }

    public function setCacheEventTable($eventTable)
    {
        $this->_tEvent = $eventTable;
    }

    /**
     * Factory design patern
     * @return \lzx\cache\Cache
     */
    public function createCache($key)
    {
        return $key[0] === '/' ? new PageCache($key) : new SegmentCache($key);
    }

    public function getCleanName($name)
    {
        static $names = [];

        if (\array_key_exists($name, $names)) {
            return $names[$name];
        }

        $_name = \trim($name);

        if (\strlen($_name) == 0 || \strpos($_name, ' ') !== false) {
            throw new \Exception('cache name is empty : ' . $name);
        }

        if ($_name[0] === '/') {
            // page uri
            if (!\strpos($_name, '#')) {
                // not previously processed
                // use # to seperate uri and query string
                if (\strpos($_name, '?')) {
                    // has query string
                    $_name = \str_replace('?', '#', $_name);
                } else {
                    $_name = $_name . '#';
                }
            } else {
                // previously processed or pre-processed name
                // validate '#'
                if (\substr_count($_name, '#') > 1) {
                    throw new \Exception('pre-processed cache name has multiple "#" charactor : ' . $name);
                }

                // validate '?'
                if (\strpos($_name, '?')) {
                    throw new \Exception('pre-processed cache name has "?" charactor : ' . $name);
                }
            }
        } else {
            // segment name or event name
            $_name = \preg_replace('/[^0-9a-z\.\_\-]/i', '_', $_name);
        }

        // save processed name to name cache
        $names[$name] = $_name;
        if ($_name != $name) {
            $names[$_name] = $_name;
        }

        return $_name;
    }

    public function getFileName(Cache $cache)
    {
        static $_filenames = [];

        $key = $cache->getKey();
        if (\array_key_exists($key, $_filenames)) {
            return $_filenames[$key];
        }

        switch (\get_class($cache)) {
            case 'lzx\cache\PageCache':
                $filename = self::$path . '/page' . $key . '.html.gz';
                break;
            case 'lzx\cache\SegmentCache':
                $filename = self::$path . '/segment/' . $key . '.txt';
                break;
            default:
                throw new \Exception('unsupport cache type: ' . \get_class($cache));
        }

        $dir = \dirname($filename);
        if (!\file_exists($dir)) {
            \mkdir($dir, 0755, true);
        }
        $_filenames[$key] = $filename;
        return $filename;
    }

    public function getID($name)
    {
        static $_ids = [];
        // found from cached id
        if (\array_key_exists($name, $_ids)) {
            return $_ids[$name];
        }

        // found from database
        $res = $this->_db->query('SELECT id FROM ' . $this->_tName . ' WHERE name = :key', [':key' => $name]);
        switch (\count($res)) {
            case 0:
                // add to database
                $this->_db->query('INSERT INTO ' . $this->_tName . ' (name) VALUEs (:key)', [':key' => $name]);
                // save to id cache
                $id = (int) $this->_db->insert_id();
                break;
            case 1:
                // save to id cache
                $id = (int) \array_pop($res[0]);
                break;
            default:
                throw new \Exception('multiple ID found for name: ' . $name);
        }
        // save to cache
        $_ids[$name] = $id;

        return $id;
    }

    public function unlinkParents($id)
    {
        $this->_db->query('DELETE FROM ' . $this->_tTree . ' WHERE cid = :cid', [':cid' => $id]);
    }

    public function linkParents($id, array $parents)
    {
        if ($parents) {
            \array_unique($parents);

            $existing = \array_column($this->_db->query('SELECT DISTINCT(pid) AS id FROM ' . $this->_tTree . ' WHERE cid = :cid', [':cid' => $id]), 'id');
            $values = [];
            foreach ($parents as $key) {
                $pid = $this->getID($key);
                if (!\in_array($pid, $existing)) {
                    $values[] = '(' . $pid . ',' . $id . ')';
                }
            }

            if ($values) {
                $this->_db->query('INSERT INTO ' . $this->_tTree . ' VALUES ' . \implode(',', $values));
            }
        }
    }

    public function getChildren($id)
    {
        $children = $this->_db->query('SELECT DISTINCT(c.id), c.name FROM ' . $this->_tName . ' AS c JOIN ' . $this->_tTree . ' AS t ON c.id = t.cid WHERE t.pid = :pid', [':pid' => $id]);
        foreach ($children as $c) {
            $this->_ids[$c['name']] = $c['id'];
        }

        return \array_column($children, 'name');
    }

    public function unlinkEvents($id)
    {
        $this->_db->query('DELETE FROM ' . $this->_tEvent . ' WHERE lid = :lid', [':lid' => $id]);
    }

    public function getEventListeners($eid, $oid)
    {
        $children = $this->_db->query('SELECT DISTINCT(c.id), c.name FROM ' . $this->_tName . ' AS c JOIN ' . $this->_tEvent . ' AS e ON c.id = e.lid WHERE e.eid = :eid AND e.oid = :oid', [':eid' => $eid, ':oid' => $oid]);
        foreach ($children as $c) {
            $this->_ids[$c['name']] = $c['id'];
        }

        return \array_column($children, 'name');
    }

    public function addEventListeners($eid, $oid, array $listeners)
    {
        if ($listeners) {
            \array_unique($listeners);

            $existing = \array_column($this->_db->query('SELECT DISTINCT(lid) AS id FROM ' . $this->_tEvent . ' WHERE eid = :eid AND oid = :oid', [':eid' => $eid, ':oid' => $oid]), 'id');
            $values = [];
            foreach ($listeners as $key) {
                $lid = $this->getID($key);
                if (!\in_array($lid, $existing)) {
                    $values[] = '(' . $eid . ',' . $oid . ',' . $lid . ')';
                }
            }

            if ($values) {
                $this->_db->query('INSERT INTO ' . $this->_tEvent . ' VALUES ' . \implode(',', $values));
            }
        }
    }
}

//__END_OF_FILE__
