<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\Cache;
use lzx\core\MySQL;

/**
 * two level tag system
 * @property $tid
 * @property $name
 * @property $description
 * @property $parent
 * @property $root
 * @property $weight
 * @property $tmp_cid
 */
class Tag extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'tags', $load_id, $fields);
      //Cache::getInstance() = Cache::getInstance();
   }

   /*
    * rarely used, only get root tags (forum / yellow page)
    */

   public static function getRootTags()
   {
      $tag = new Tag();
      $tag->where('parent', NULL, 'IS');
      return $tag->getList();
   }

   /*
    * rarely used, only get leaves for a root tid (forum / yellow page)
    */

   public static function getLeafTags($tid, $fields = '')
   {
      static $leafTags = array();

      if (!\array_key_exists($tid, $leafTags))
      {
         $tag = new Tag();
         $tag->where('parent', NULL, 'IS NOT');
         $tag->where('root', $tid, '=');
         $tag->order('weight');
         $leafTags[$tid] = $tag->getList($fields);
      }
      return $leafTags[$tid];
   }

   /*
    * rarely used, only get leaves for a root tid (forum / yellow page)
    */

   public static function getLeafTIDs($tid)
   {
      static $leafTIDs = array();

      if (!\array_key_exists($tid, $leafTIDs))
      {
         $tags = self::getLeafTags($tid, 'tid');
         $tids = array();
         foreach ($tags as $t)
         {
            $tids[] = $t['tid'];
         }
         $leafTIDs[$tid] = $tids;
      }
      return $leafTIDs[$tid];
   }

   /*
    * get the tag tree, upto 2 levels
    */

   public function getTagTree($fields = '')
   {
      $tag = new Tag();
      $tag->tid = $this->tid;
      $tagtree = $tag->getList($fields, 1);

      if (\sizeof($tagtree) == 0)
      {
         return NULL;
      }
      else
      {
         $tagtree = $tagtree[0];
      }

      if (\is_null($tagtree['parent'])) // root tag, has 0/1/2 children level
      {
         $children = $tag->getChildren($fields);
         if (\sizeof($children) > 0)
         {
            foreach ($children as $i => $t)
            {
               $child_tag = new Tag($t['tid']);
               $grandchildren = $child_tag->getChildren($fields);
               if (\sizeof($grandchildren) > 0)
               {
                  $children[$i]['children'] = $grandchildren;
               }
            }
            $tagtree['children'] = $children;
         }
      }
      else // non root tag, only has 0/1 children level
      {
         $children = $tag->getChildren($fields);
         if (\sizeof($children) > 0)
         {
            $tagtree['children'] = $children;
         }
      }

      return $tagtree;
   }

   /*
    * get parent tag
    */

   public function getParent($fields = '')
   {
      $this->load('parent');
      if (\is_null($this->parent))
      {
         return NULL;
      }
      else
      {
         $tag = new Tag();
         $tag->tid = $this->parent;
         $parent = $tag->getList($fields, 1);
         return \array_pop($parent);
      }
   }

   /*
    * get children tags
    */

   public function getChildren($fields = '')
   {
      $tag = new Tag();
      $tag->parent = $this->tid;
      $tag->order('weight');
      return $tag->getList($fields);
   }

   // get the information for the latest updated node
   public function getNodeInfo()
   {
      $tag = new Tag($this->tid, 'tid');
      if ($tag->exists())
      {
         $sql = 'SELECT (SELECT count(*) FROM nodes WHERE tid = ' . $this->tid . ') AS nodeCount,'
            . ' (SELECT count(*) FROM comments WHERE tid = ' . $this->tid . ') AS commentCount';
         $info = $this->_db->row($sql);
         $sql = 'SELECT n.nid, n.title, n.uid, u.username, n.createTime '
            . 'FROM nodes AS n JOIN users AS u ON n.uid = u.uid '
            . 'WHERE n.tid = ' . $this->tid . ' AND n.status > 0 AND u.status > 0 '
            . 'ORDER BY n.createTime DESC '
            . 'LIMIT 1';
         $node = $this->_db->row($sql);
         $sql = 'SELECT c.nid, n.title, c.uid, u.username, c.createTime '
            . 'FROM comments AS c JOIN nodes AS n ON c.nid = n.nid JOIN users AS u ON c.uid = u.uid '
            . 'WHERE c.tid = ' . $this->tid . ' AND n.status > 0 AND u.status > 0 '
            . 'ORDER BY c.createTime DESC '
            . 'LIMIT 1';
         $comment = $this->_db->row($sql);
         $info = \array_merge($info, $node['createTime'] > $comment['createTime'] ? $node : $comment);
         return $info;
      }
   }

   /*
    * create menu tree for root tags
    */

   public static function createMenu($type)
   {
      $tag = new Tag();
      if ($type == 'forum')
      {
         $tag->tid = 1;
      }
      elseif ($type == 'yp')
      {
         $tag->tid = 2;
      }
      else
      {
         return;
      }

      $tree = $tag->getTagTree();

      $liMenu = '';

      foreach ($tree['children'] as $branch)
      {
         $liMenu .= '<li><a title="' . $branch['name'] . '" href="/' . $type . '/' . $branch['tid'] . '">' . $branch['name'] . '</a>';
         $liMenu .= '<ul style="display: none;">';
         foreach ($branch['children'] as $leaf)
         {
            $liMenu .= '<li><a title="' . $leaf['name'] . '" href="/' . $type . '/' . $leaf['tid'] . '">' . $leaf['name'] . '</a></li>';
         }
         $liMenu .= '</ul>';
         $liMenu .= '</li>';
      }

      return $liMenu;
   }

   /*
    * import function from old table to new table, rarely used in production
    */

   public function importTag()
   {
      $this->importRoot();
      $root_tids = $this->_db->select('SELECT tid FROM tags');
      foreach ($root_tids as $t)
      {
         $this->importLeaf($t['tid']);
      }
   }

   public function importRoot()
   {
      $cate = new Category();
      $cate->where('parent', NULL, 'IS');
      $cate->order('weight');
      $root_tags = $cate->getList();
      $weight = 1;

      foreach ($root_tags as $t)
      {
         $tag = new Tag();
         $tag->tmp_cid = $t['cid'];
         $tag->name = $t['name'];
         $tag->description = $t['description'];
         $tag->weight = $weight;
         $tag->save();
         $weight++;
         $tag->root = $tag->tid;
         $tag->update('root');
         echo $t['name'] . PHP_EOL;
      }
   }

   public function importLeaf($root_tid)
   {
      $cate = new Category();
      $root_cid = $this->_db->val('SELECT tmp_cid FROM tags WHERE tid = ' . $root_tid);
      $cate->where('parent', $root_cid, '=');
      $cate->order('weight');
      $leaf1 = $cate->getList();
      $weight = 1;
      foreach ($leaf1 as $t)
      {
         $tag = new Tag();
         $tag->tmp_cid = $t['cid'];
         $tag->name = $t['name'];
         $tag->description = $t['description'];
         $tag->parent = $root_tid;
         $tag->weight = $weight;
         $tag->root = $root_tid;
         $tag->save();
         $weight++;
         echo $t['name'] . PHP_EOL;
      }

      foreach ($leaf1 as $t)
      {
         $weight = 1;
         $tag = new Tag();
         $cate->where('parent', $t['cid'], '=');
         $cate->order('weight');
         $leaf2 = $cate->getList();
         $parent_tid = $this->_db->val('SELECT tid FROM tags WHERE tmp_cid = ' . $t['cid']);
         foreach ($leaf2 as $t)
         {
            $tag = new Tag();
            $tag->tmp_cid = $t['cid'];
            $tag->name = $t['name'];
            $tag->description = $t['description'];
            $tag->parent = $parent_tid;
            $tag->weight = $weight;
            $tag->root = $root_tid;
            $tag->save();
            $weight++;
            echo $t['name'] . PHP_EOL;
         }
      }
   }

   public function buildNodeTagMap()
   {
      $this->_db->query('UPDATE nodes SET tid = (SELECT tid FROM tags WHERE tmp_cid = nodes.tmp_cid)');
      $this->_db->query('UPDATE comments, nodes SET comments.tid = nodes.tid WHERE comments.nid = nodes.nid');
      $this->_db->query('DELETE FROM `comments` WHERE tid IS NULL');
      $this->_db->query('ALTER TABLE `comments` ADD INDEX ( `tid` ) ');
   }

}

?>
