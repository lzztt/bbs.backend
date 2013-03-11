<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\DataObject;
use lzx\core\Cache;
use lzx\core\MySQL;

/**
 * two level category system
 * @property $cid
 * @property $name
 * @property $description
 * @property $parent
 * @property $weight
 * @property $nodeCount
 */
class Category extends DataObject
{

   public function __construct($load_id = null, $fields = '')
   {
      $db = MySQL::getInstance();
      parent::__construct($db, 'categories', $load_id, $fields);
      //Cache::getInstance() = Cache::getInstance();
   }

   public function getChildrenTree($cid, $nodeInfo = FALSE)
   {
      $cache_key = 'cate_tree_' . $cid . ($nodeInfo ? '_node' : '');
      $tree = Cache::getInstance()->fetch($cache_key);

      if ($tree !== FALSE)
      {
         return unserialize($tree);
      }

      if ($nodeInfo)
      {
         $cache_key_clean_tree = 'cate_tree_' . $cid; // shortcut for nodeInfo tree, we will check if the clean tree exist first
         $tree = Cache::getInstance()->fetch($cache_key_clean_tree);

         if ($tree !== FALSE)
         {
            $tree = unserialize($tree);
            // add nodeInfo for tree leafs
            if (is_null($tree['children'])) // only itself, itself is the leaf, no child here, we don't honor $nodeInfo if the cid is a leaf cid
            {
               //$tree['nodeInfo'] = $this->getNodeInfo($tree['cid']);
            }
            else
            {
               foreach ($tree['children'] as &$child)
               {
                  if (is_null($child['children']))
                  {
                     $child['nodeInfo'] = $this->getNodeInfo($child['cid']);
                  }
                  else
                  {
                     foreach ($child['children'] as &$gchild)
                     {
                        $gchild['nodeInfo'] = $this->getNodeInfo($gchild['cid']);
                     }
                  }
               }
            }
//            Cache::getInstance()->store($cache_key, serialize($tree));
            return $tree;
         }
      }

      // every try from cache fails, will build the tree from scratch
      $sql = 'SELECT * FROM categories '
         . 'WHERE cid = ' . $cid . ' '
         . 'OR cid IN (SELECT cid FROM categories WHERE parent = ' . $cid . ') '
         . 'OR cid IN (SELECT cid FROM categories WHERE parent IN (SELECT cid FROM categories WHERE parent = ' . $cid . '))';
      // it is meaningless if we sort by weight here, because we will use the cid as the array key anyway

      $rows = $this->_db->select($sql);

      // empty
      if (empty($rows))
      {
         return $rows;
      }

      // only itself, itself is the leaf, no child here, we don't honor $nodeInfo if the cid is a leaf cid
      if (sizeof($rows) == 1)
      {
         $tree = $rows[0];
      }
      else
      {
         // at least two rows (one child) here, leaf will be a child (will always has parent) AND is not a parent for others (will never has child) itself now
         foreach ($rows as $r)
         {
            $_self[$r['cid']] = $r;
            if ($r['cid'] != $cid)
               $_children[$r['parent']][$r['cid']] = $r;
         }

         $leafs = array_diff(array_keys($_self), array_keys($_children));

         if ($nodeInfo)
         {
            foreach ($leafs as $_cid)
            { // will only use $_children array, so only update this one with nodeInfo
               $_children[$_self[$_cid]['parent']][$_cid]['nodeInfo'] = $this->getNodeInfo($_cid);
            }
         }
         // build the tree, sort the tree by weight
         $tree = $_self[$cid];

         if (isset($_children[$cid]))
         {
            $tree['children'] = $this->sortByWeight($_children[$cid]);

            foreach ($tree['children'] as &$child)
            {
               if (isset($_children[$child['cid']]))
               {
                  $child['children'] = $this->sortByWeight($_children[$child['cid']]);
               }
            }
         }
      }

      if (!$nodeInfo)
      {
         Cache::getInstance()->store($cache_key, serialize($tree));
      }

      return $tree;
   }

   // get the information for the latest updated node
   private function getNodeInfo($cid)
   {
      $sql = 'SELECT (SELECT count(*) FROM nodes WHERE cid = ' . $cid . ') AS nodeCount, n.nid AS nodeID, n.title as nodeTitle, '
         . 'IFNULL(c.createTime, n.createTime) AS lastUpdateTime, '
         . 'IFNULL((SELECT username FROM users WHERE uid=c.uid), (SELECT username FROM users WHERE uid = n.uid)) AS lastUpdateAuthor '
         . 'FROM '
         . 'nodes AS n '
         . 'LEFT JOIN comments AS c ON n.nid = c.nid '
         . 'WHERE n.cid = ' . $cid . ' AND n.status = 1 '
         . 'ORDER BY lastUpdateTime DESC '
         . 'LIMIT 1';

      return $this->_db->row($sql);
   }

   public function getForumNodes($cid, $limit = false, $offset = false)
   {
      if (is_array($cid))
         $where = 'IN (' . implode(',', $cid) . ')';
      else
         $where = '= ' . (int) $cid;

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      if (UA === 'robot')
      {
         $sql = 'SELECT n.nid, n.title,'
            . ' IFNULL((SELECT createTime FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            . ' FROM nodes AS n'
            . ' WHERE n.status = 1 AND n.cid ' . $where
            . ' ORDER BY lastUpdateTime DESC ' . $limit . ' ' . $offset;
      }
      else
      {
         $sql = 'SELECT n.nid, n.title, n.isSticky, n.viewCount, n.createTime,'
            . ' (SELECT createTime FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1) AS lastCommentTime,'
            . ' (SELECT username FROM users WHERE uid = n.uid) AS createrName,'
            . ' (SELECT username FROM users WHERE uid = (SELECT uid FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1)) AS lastCommenterName,'
            . ' (SELECT count(*) FROM comments WHERE nid = n.nid) AS commentCount,'
            . ' IFNULL((SELECT createTime FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            // . 'IFNULL((SELECT username FROM users WHERE uid=c.uid), (SELECT username FROM users WHERE uid = n.uid)) AS lastUpdateAuthor '
            . ' FROM nodes AS n'
            . ' WHERE n.status = 1 AND n.cid ' . $where
            . ' ORDER BY n.isSticky DESC, lastUpdateTime DESC ' . $limit . ' ' . $offset;
      }
      return $this->_db->select($sql);
   }

   public function getYellowPageNodes($cid, $limit = false, $offset = false)
   {
      if (is_array($cid))
         $where = 'IN (' . implode(',', $cid) . ')';
      else
         $where = '= ' . (int) $cid;

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      if (UA === 'robot')
      {
         $sql = 'SELECT n.nid, n.title, n.body,'
            . ' IFNULL((SELECT createTime FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            . ' FROM nodes AS n'
            . ' WHERE n.status = 1 AND n.cid ' . $where
            . ' ORDER BY lastUpdateTime DESC ' . $limit . ' ' . $offset;
      }
      else
      {
         $sql = 'SELECT n.nid, n.title, n.body, n.viewCount, IFNULL(r.ratingAvg, 0) AS ratingAvg, IFNULL(r.ratingCount, 0) AS ratingCount,'
            . ' (SELECT count(*) FROM comments WHERE nid = n.nid) AS commentCount,'
            . ' IFNULL((SELECT createTime FROM comments WHERE nid = n.nid ORDER BY createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            . ' FROM nodes AS n'
            . ' LEFT JOIN (SELECT nid, avg(rating) AS ratingAvg, count(*) AS ratingCount FROM yp_rating GROUP BY nid) AS r ON r.nid = n.nid'
            . ' WHERE n.status = 1 AND n.cid ' . $where
            . ' ORDER BY lastUpdateTime DESC ' . $limit . ' ' . $offset;
      }
      return $this->_db->select($sql);
   }

   private function sortByWeight($group)
   {
      foreach ($group as $k => $g)
      {
         $tmp[$k] = $g['weight'];
      }

      asort($tmp);

      foreach (array_keys($tmp) as $k)
      {
         $sort[$k] = $group[$k];
      }

      return $sort;
   }

   /*
     public function getChildren($cid=null)
     {
     if (is_null($cid))
     {
     if (is_null($this->cid))
     {
     $this->logger->warn('no cid');
     return null;
     }
     $cid = $this->cid;
     }
     $res = $this->_db->select('SELECT cid FROM categories where parent = ' . $cid . ' ORDER BY weight ASC');

     $children = array();
     foreach ($res as $r)
     {
     $children[] = (int) $r['cid'];
     }

     return $children;
     }
    */

   public function getParentTree($cid)
   {
      $arr = $this->_db->select('SELECT c.* FROM categories AS c WHERE c.cid IN ((SELECT parent FROM categories WHERE cid = ' . $cid . '), ' . $cid . ') OR c.parent IS NULL');

      foreach ($arr as $r)
      {
         $tmp[$r['cid']] = $r;
      }

      $cate = $tmp[$cid];
      $cates[] = $cate; // add self
      // add parents
      while (isset($cate['parent']))
      {
         $cate = $tmp[$cate['parent']];
         $cates[] = $cate;
      }

      return array_reverse($cates);
   }

   public function createMenu($type)
   {
      if ($type == 'forum')
         $cid = 2;
      elseif ($type == 'yp')
         $cid = 3;
      else
         return;

      $tree = $this->getChildrenTree($cid);

      $liMenu = '';

      foreach ($tree['children'] as $branch)
      {
         $liMenu .= '<li><a title="' . $branch['name'] . '" href="/' . $type . '/' . $branch['cid'] . '">' . $branch['name'] . '</a>';
         $liMenu .= '<ul style="visibility: hidden; display: none;">';
         foreach ($branch['children'] as $leaf)
         {
            $liMenu .= '<li><a title="' . $leaf['name'] . '" href="/' . $type . '/' . $leaf['cid'] . '">' . $leaf['name'] . '</a></li>';
         }
         $liMenu .= '</ul>';
         $liMenu .= '</li>';
      }

      return $liMenu;
   }

   public function getLeafCIDs($type)
   {
      $cache_key = $type . 'LeafCIDs';
      $cids = Cache::getInstance()->fetch($cache_key);
      if ($cids !== FALSE)
      {
         $cids = unserialize($cids);
      }
      else
      {
         if ($type == 'forum')
            $cid = 2;
         elseif ($type == 'yp')
            $cid = 3;
         else
            return;

         $tree = $this->getChildrenTree($cid);
         var_dump($tree);

         $cids = array();

         foreach ($tree['children'] as $branch)
         {
            foreach ($branch['children'] as $leaf)
            {
               $cids[] = $leaf['cid'];
            }
         }
         Cache::getInstance()->store($cache_key, serialize($cids));
      }

      return $cids;
   }

}

//__END_OF_FILE__
