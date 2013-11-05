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

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

   /*
    * rarely used, only get root tags (forum / yellow page)
    */

   public static function getRootTags()
   {
      $tag = new Tag();
      $tag->where( 'parent', NULL, 'IS' );
      return $tag->getList();
   }

   /*
    * rarely used, only get leaves for a root tid (forum / yellow page)
    */

   public static function getLeafTags( $tid, $fields = '' )
   {
      static $leafTags = array( );

      if ( !\array_key_exists( $tid, $leafTags ) )
      {
         $tag = new Tag();
         $tag->where( 'parent', NULL, 'IS NOT' );
         $tag->where( 'root', $tid, '=' );
         $tag->order( 'weight' );
         $leafTags[$tid] = $tag->getList( $fields );
      }
      return $leafTags[$tid];
   }

   /*
    * rarely used, only get leaves for a root tid (forum / yellow page)
    */

   public static function getLeafTIDs( $tid )
   {
      static $leafTIDs = array( );

      if ( !\array_key_exists( $tid, $leafTIDs ) )
      {
         $tags = self::getLeafTags( $tid, 'tid' );
         $tids = array( );
         foreach ( $tags as $t )
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

   public function getTagTree( $fields = '' )
   {
      $tag = new Tag();
      $tag->tid = $this->tid;
      $tagtree = $tag->getList( $fields, 1 );

      if ( \sizeof( $tagtree ) == 0 )
      {
         return NULL;
      }
      else
      {
         $tagtree = $tagtree[0];
      }

      if ( \is_null( $tagtree['parent'] ) ) // root tag, has 0/1/2 children level
      {
         $children = $tag->getChildren( $fields );
         if ( \sizeof( $children ) > 0 )
         {
            foreach ( $children as $i => $t )
            {
               $child_tag = new Tag( $t['tid'] );
               $grandchildren = $child_tag->getChildren( $fields );
               if ( \sizeof( $grandchildren ) > 0 )
               {
                  $children[$i]['children'] = $grandchildren;
               }
            }
            $tagtree['children'] = $children;
         }
      }
      else // non root tag, only has 0/1 children level
      {
         $children = $tag->getChildren( $fields );
         if ( \sizeof( $children ) > 0 )
         {
            $tagtree['children'] = $children;
         }
      }

      return $tagtree;
   }

   /*
    * get parent tag
    */

   public function getParent( $fields = '' )
   {
      $this->load( 'parent' );
      if ( \is_null( $this->parent ) )
      {
         return NULL;
      }
      else
      {
         $tag = new Tag();
         $tag->tid = $this->parent;
         $parent = $tag->getList( $fields, 1 );
         return \array_pop( $parent );
      }
   }

   /*
    * get children tags
    */

   public function getChildren( $fields = '' )
   {
      $tag = new Tag();
      $tag->parent = $this->tid;
      $tag->order( 'weight' );
      return $tag->getList( $fields );
   }

   // get the information for the latest updated node
   public function getNodeInfo()
   {
      $tag = new Tag( $this->tid, 'tid' );
      if ( $tag->exists() )
      {
         $sql = 'SELECT (SELECT count(*) FROM Node WHERE tid = ' . $this->tid . ') AS nodeCount,'
               . ' (SELECT count(*) FROM Comment WHERE tid = ' . $this->tid . ') AS commentCount';
         $info = $this->_db->row( $sql );
         $sql = 'SELECT n.nid, n.title, n.uid, u.username, n.createTime '
               . 'FROM Node AS n JOIN User AS u ON n.uid = u.uid '
               . 'WHERE n.tid = ' . $this->tid . ' AND n.status > 0 AND u.status > 0 '
               . 'ORDER BY n.createTime DESC '
               . 'LIMIT 1';
         $node = $this->_db->row( $sql );
         $sql = 'SELECT c.nid, n.title, c.uid, u.username, c.createTime '
               . 'FROM Comment AS c JOIN Node AS n ON c.nid = n.nid JOIN User AS u ON c.uid = u.uid '
               . 'WHERE c.tid = ' . $this->tid . ' AND n.status > 0 AND u.status > 0 '
               . 'ORDER BY c.createTime DESC '
               . 'LIMIT 1';
         $comment = $this->_db->row( $sql );
         $info = \array_merge( $info, $node['createTime'] > $comment['createTime'] ? $node : $comment );
         return $info;
      }
   }

   /*
    * create menu tree for root tags
    */

   public static function createMenu( $type )
   {
      $tag = new Tag();
      if ( $type == 'forum' )
      {
         $tag->tid = 1;
      }
      elseif ( $type == 'yp' )
      {
         $tag->tid = 2;
      }
      else
      {
         return;
      }

      $tree = $tag->getTagTree();

      $liMenu = '';

      foreach ( $tree['children'] as $branch )
      {
         $liMenu .= '<li><a title="' . $branch['name'] . '" href="/' . $type . '/' . $branch['tid'] . '">' . $branch['name'] . '</a>';
         $liMenu .= '<ul style="display: none;">';
         foreach ( $branch['children'] as $leaf )
         {
            $liMenu .= '<li><a title="' . $leaf['name'] . '" href="/' . $type . '/' . $leaf['tid'] . '">' . $leaf['name'] . '</a></li>';
         }
         $liMenu .= '</ul>';
         $liMenu .= '</li>';
      }

      return $liMenu;
   }

}

?>
