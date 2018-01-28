<?php declare(strict_types=1);

namespace site\dbobject;

use Exception;
use lzx\db\DB;
use lzx\db\DBObject;

class Tag extends DBObject
{
    public $id;
    public $name;
    public $description;
    public $parent;
    public $root;
    public $weight;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'tags', $id, $properties);
    }

    public function getLeafTIDs(): array
    {
        if ($this->id) {
            $ids = [];
            $tree = $this->getTagTree();
            foreach ($tree as $i => $t) {
                if (!array_key_exists('children', $t)) {
                    $ids[] = $i;
                }
            }
            return $ids;
        }
        return [];
    }

    public function getTagRoot(): array
    {
        static $root = [];

        if (isset($this->id)) {
            if (!array_key_exists($this->id, $root)) {
                $arr = array_reverse($this->call('get_tag_root(' . $this->id . ')'));

                $tags = [];
                foreach ($arr as $r) {
                    $id = (int) $r['id'];
                    $parent = is_null($r['parent']) ? null : (int) $r['parent'];
                    $tags[$id] = [
                        'id' => $id,
                        'name' => $r['name'],
                        'description' => $r['description'],
                        'parent' => $parent
                    ];
                }
                $root[$this->id] = $tags;
            }
            return $root[$this->id];
        } else {
            throw new Exception('no tag id set');
        }
    }

    public function getTagTree(): array
    {
        static $tree = [];

        if (isset($this->id)) {
            if (!array_key_exists($this->id, $tree)) {
                $arr = $this->call('get_tag_tree(' . $this->id . ')');

                $tags = [];
                $children = [];
                foreach ($arr as $r) {
                    $id = (int) $r['id'];
                    $parent = is_null($r['parent']) ? null : (int) $r['parent'];

                    $tags[$id] = [
                        'id' => $id,
                        'name' => $r['name'],
                        'description' => $r['description'],
                        'parent' => $parent,
                    ];
                    if (!is_null($parent)) {
                        $children[$parent][] = $id;
                    }
                }

                foreach ($children as $p => $c) {
                    $tags[$p]['children'] = $c;
                }

                $tree[$this->id] = $tags;
            }

            return $tree[$this->id];
        } else {
            throw new Exception('no tag id set');
        }
    }

    // get the information for the latest updated node
    public function getNodeInfo(): array
    {
        return $this->call('get_tag_node_info_1(' . $this->id . ')');
    }
}
