<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\core\Cache;
use lzx\db\DB;

/**
 * two level tag system
 * @property $id
 * @property $name
 * @property $description
 * @property $parent
 * @property $root
 * @property $weight
 */
class Tag extends DBObject
{

    const FORUM_ID = 1;
    const YP_ID = 2;

    public function __construct( $id = null, $properties = '' )
    {
        $db = DB::getInstance();
        $table = 'tags';
        $fields = [
            'id' => 'id',
            'name' => 'name',
            'description' => 'description',
            'parent' => 'parent',
            'root' => 'root',
            'weight' => 'weight'
        ];
        parent::__construct( $db, $table, $fields, $id, $properties );
    }

    /*
     * rarely used, only get leaves for a root id (forum / yellow page)
     */

    public function getLeafTIDs()
    {
        if ( $this->id )
        {
            $ids = [];
            $tree = $this->getTagTree();
            foreach ( $tree as $i => $t )
            {
                if ( !\array_key_exists( 'children', $t ) )
                {
                    $ids[] = $i;
                }
            }
            return $ids;
        }
        return [];
    }

    /*
     * get the tag tree, upto 2 levels
     */

    public function getTagRoot()
    {
        static $root = [];

        if ( isset( $this->id ) )
        {
            if ( !\array_key_exists( $this->id, $root ) )
            {
                $arr = \array_reverse( $this->call( 'get_tag_root(' . $this->id . ')' ) );

                $tags = [];
                foreach ( $arr as $r )
                {
                    $id = \intval( $r['id'] );
                    $parent = \is_null( $r['parent'] ) ? NULL : \intval( $r['parent'] );
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
        }
        else
        {
            throw new \Exception( 'no tag id set' );
        }
    }

    public function getTagTree()
    {
        static $tree = [];

        if ( isset( $this->id ) )
        {
            if ( !\array_key_exists( $this->id, $tree ) )
            {
                $arr = $this->call( 'get_tag_tree(' . $this->id . ')' );

                $tags = [];
                $children = [];
                foreach ( $arr as $r )
                {
                    $id = \intval( $r['id'] );
                    $parent = \is_null( $r['parent'] ) ? NULL : \intval( $r['parent'] );

                    $tags[$id] = [
                        'id' => $id,
                        'name' => $r['name'],
                        'description' => $r['description'],
                        'parent' => $parent,
                    ];
                    if ( !\is_null( $parent ) )
                    {
                        $children[$parent][] = $id;
                    }
                }

                foreach ( $children as $p => $c )
                {
                    $tags[$p]['children'] = $c;
                }

                $tree[$this->id] = $tags;
            }

            return $tree[$this->id];
        }
        else
        {
            throw new \Exception( 'no tag id set' );
        }
    }

    /*
     * get parent tag
     */

    public function getParent( $properties = '' )
    {
        $this->load( 'parent' );
        if ( \is_null( $this->parent ) )
        {
            return NULL;
        }
        else
        {
            $tag = new Tag();
            $tag->id = $this->parent;
            $parent = $tag->getList( $properties, 1 );
            return \array_pop( $parent );
        }
    }

    /*
     * get children tags
     */

    public function getChildren( $properties = '' )
    {
        $tag = new Tag();
        $tag->parent = $this->id;
        $tag->order( 'weight' );
        return $tag->getList( $properties );
    }

    // get the information for the latest updated node
    public function getNodeInfo()
    {
        return $this->call( 'get_tag_node_info_1(' . $this->id . ')' );
    }

}

//__END_OF_FILE__
