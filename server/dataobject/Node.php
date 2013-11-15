<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dataobject;

use lzx\core\MySQL;
use lzx\core\DataObject;
use site\dataobject\Tag;

/**
 * @property $nid
 * @property $tid
 * @property $uid
 * @property $createTime
 * @property $lastModifiedTime
 * @property $title
 * @property $body
 * @property $hash
 * @property $viewCount
 * @property $weight
 * @property $status
 */
class Node extends DataObject
{

   public function __construct( $load_id = null, $fields = '' )
   {
      $db = MySQL::getInstance();
      $table = \array_pop( \explode( '\\', __CLASS__ ) );
      parent::__construct( $db, $table, $load_id, $fields );
   }

   public function getHash()
   {
      return \md5( $this->uid . '_' . $this->title . '_' . $this->body, TRUE );
   }

   public function add()
   {
      // CHECK USER
      $userInfo = $this->_db->row( 'SELECT createTime, lastAccessIPInt, status FROM User WHERE uid = ' . $this->uid );
      if ( \intval( $userInfo['status'] ) != 1 )
      {
         throw new \Exception( 'This User account cannot post message.' );
      }
      $days = \intval( ($this->createTime - $userInfo['createTime']) / 86400 );
      // registered less than 1 month
      if ( $days < 30 )
      {
         $geo = \geoip_record_by_name( \long2ip( $userInfo['lastAccessIPInt'] ) );
         // not from Texas
         if ( !$geo || $geo['region'] != 'TX' )
         {
            //select (select count(*) from Node where uid = 126 and createTime > unix_timestamp() - 86400) + ( select count(*) from Comment where uid = 126 and createTime > unix_timestamp() - 86400) AS count
            $oneday = \intval( $this->createTime - 86400 );
            $count = $this->_db->val(
                  'SELECT 
                  ( SELECT count(*) FROM Node WHERE uid = ' . $this->uid . ' AND createTime > ' . $oneday . ' ) +
                  ( SELECT count(*) FROM Comment WHERE uid = ' . $this->uid . ' AND createTime > ' . $oneday . ' ) AS c'
            );
            if ( $count >= $days )
            {
               throw new \Exception( 'Quota limitation reached!<br />Your account is ' . $days . ' days old, so you can only post ' . $days . ' messages within 24 hours. <br /> You already have ' . $count . ' message posted in last 24 hours. Please wait for several hours to get more quota.' );
            }
         }
      }

      // check spam
      $nodes = $this->_db->select( 'SELECT createTime FROM ' . $this->_table . ' WHERE uid = ' . $this->uid . ' AND createTime > ' . (\intval( $this->createTime ) - 86400) . ' ORDER BY createTime DESC' );
      $count = \sizeof( $nodes );

      if ( $count > 0 )
      {
         // limit 1 node within 3 minute
         if ( $this->createTime - \intval( $nodes[0]['createTime'] ) < 180 )
         {
            throw new \Exception( 'You are posting too fast. Please slow down.' );
         }
      }

      if ( $count > 2 )
      {
         // limit 3 nodes within 30 minutes
         if ( $this->createTime - \intval( $nodes[2]['createTime'] ) < 1800 )
         {
            throw new \Exception( 'You are posting too fast. Please slow down.' );
         }
      }

      if ( $count > 5 )
      {
         // limit 6 nodes within 12 hours
         if ( $this->createTime - \intval( $nodes[5]['createTime'] ) < 43200 )
         {
            throw new \Exception( 'too many nodes posted within 12 hours' );
         }
      }

      // check duplicate
      $this->hash = $this->getHash();

      $count = $this->_db->val( 'SELECT count(*) FROM ' . $this->_table . ' WHERE hash = ' . $this->_db->str( $this->hash ) . ' AND createTime > ' . (\intval( $this->createTime ) - 86400) );
      if ( $count > 0 )
      {
         throw new \Exception( 'duplicate node found' );
      }

      parent::add();
   }

   public function update( $fields = '' )
   {
      // check spam
      // no need for update, only need for create
      // check duplicate      
      if ( $fields == '' )
      {
         $this->hash = $this->getHash();
      }
      else
      {
         $f = \explode( ',', $fields );
         if ( \in_array( 'title', $f ) || \in_array( 'body', $f ) )
         {
            $this->hash = $this->getHash();
            if ( !\in_array( 'hash', $f ) )
            {
               $fields .=',hash';
            }
         }
      }

      if ( isset( $this->hash ) )
      {
         $count = $this->_db->val( 'SELECT count(*) FROM ' . $this->_table . ' WHERE hash = ' . $this->_db->str( $this->hash ) . ' AND createTime > ' . (\intval( $this->lastModifiedTime ) - 86400) . ' AND nid != ' . $this->nid );
         if ( $count > 0 )
         {
            throw new \Exception( 'duplicate node found' );
         }
      }

      parent::update( $fields );
   }

   public function getForumNodeList( $tid, $limit = FALSE, $offset = FALSE )
   {
      if ( \is_array( $tid ) )
      {
         $where = 'IN (' . \implode( ',', $tid ) . ')';
      }
      else
      {
         $where = '= ' . (int) $tid;
      }

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      $sql = 'SELECT n.nid, n.title, n.weight, n.viewCount, n.createTime, n.uid AS createrUID, u.username AS createrName, '
            . 'IFNULL((SELECT c.createTime FROM Comment AS c WHERE c.nid = n.nid ORDER BY c.createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime '
            . 'FROM Node AS n JOIN User AS u ON n.uid = u.uid '
            . 'WHERE (n.status > 0 AND n.tid = ' . (int) $tid . ') OR n.nid IN (22860, 23200, 25295) '
            . 'ORDER BY n.weight DESC, lastUpdateTime DESC ' . $limit . ' ' . $offset;
// YING
      $list = $this->_db->select( $sql );

      foreach ( $list as $i => $n )
      {
         $sql = 'SELECT c.uid AS lastCommenterUID, u.username AS lastCommenterName, c.createTime AS lastCommentTime, '
               . '(SELECT count(*) FROM Comment WHERE nid =' . $n['nid'] . ') AS commentCount '
               . 'FROM Comment AS c JOIN User AS u ON c.uid = u.uid '
               . 'WHERE c.nid = ' . $n['nid'] . ' ORDER BY c.createTime DESC LIMIT 1';
         $comment = $this->_db->row( $sql );
         $list[$i] = \array_merge( $list[$i], $comment );
      }

      return $list;
   }

   public function getForumNode( $nid )
   {
      /* we are using the last one
        mysql> explain SELECT n.*, author.* FROM Node AS n LEFT JOIN (SELECT u.uid, u.username, u.sex, u.signature, u.createTime, u.avatar, u.badge, u.points, uo.time AS lastAccessTime FROM User AS u LEFT JOIN UserOnline AS uo ON u.uid = uo.uid) as author on n.uid = author.uid WHERE n.nid = 1;
        +----+-------------+------------+-------+---------------+---------+---------+------------+------+-------+
        | id | select_type | table      | type  | possible_keys | key     | key_len | ref        | rows | Extra |
        +----+-------------+------------+-------+---------------+---------+---------+------------+------+-------+
        |  1 | PRIMARY     | n          | const | PRIMARY       | PRIMARY | 4       | const      |    1 |       |
        |  1 | PRIMARY     | <derived2> | ALL   | NULL          | NULL    | NULL    | NULL       | 3023 |       |
        |  2 | DERIVED     | u          | ALL   | NULL          | NULL    | NULL    | NULL       | 3023 |       |
        |  2 | DERIVED     | uo         | ref   | uid           | uid     | 5       | hbbs.u.uid |    1 |       |
        +----+-------------+------------+-------+---------------+---------+---------+------------+------+-------+
        4 rows in set (0.08 sec)

        mysql> explain SELECT na.*, uo.time AS lastAccessTime FROM (SELECT n.*, u.sex, u.signature, u.createTime as uCreateTime, u.avatar, u.badge, u.points FROM Node AS n LEFT JOIN User AS u ON n.uid = u.uid WHERE n.nid = 1) as na LEFT JOIN UserOnline AS uo ON na.uid = uo.uid;
        +----+-------------+------------+--------+---------------+---------+---------+-------+------+-------+
        | id | select_type | table      | type   | possible_keys | key     | key_len | ref   | rows | Extra |
        +----+-------------+------------+--------+---------------+---------+---------+-------+------+-------+
        |  1 | PRIMARY     | <derived2> | system | NULL          | NULL    | NULL    | NULL  |    1 |       |
        |  1 | PRIMARY     | uo         | system | uid           | NULL    | NULL    | NULL  |    1 |       |
        |  2 | DERIVED     | n          | const  | PRIMARY       | PRIMARY | 4       |       |    1 |       |
        |  2 | DERIVED     | u          | const  | PRIMARY       | PRIMARY | 4       | const |    1 |       |
        +----+-------------+------------+--------+---------------+---------+---------+-------+------+-------+
        4 rows in set (0.00 sec)
       */

      $sql = 'SELECT n.*,'
            . ' (SELECT COUNT(*) FROM Comment AS c WHERE c.nid = ' . $nid . ') AS commentCount,'
            . ' u.username, u.sex, u.signature, u.createTime as joinTime, u.lastAccessIPInt as accessIP, u.avatar, u.badge, u.points'
            . ' FROM Node AS n JOIN User AS u ON n.uid = u.uid'
            . ' WHERE n.status > 0 AND u.status > 0 AND n.nid = ' . $nid;
      $arr = $this->_db->row( $sql );

      if ( \sizeof( $arr ) > 0 )
      {
         $arr['files'] = $this->_db->select( 'SELECT fid, name, path FROM Image WHERE cid IS NULL AND nid = ' . $nid . ' ORDER BY fid ASC' );
         return $arr;
      }
      else
      {
         return NULL;
      }
   }

   public function getForumNodeComments( $nid, $limit = false, $offset = false )
   {
      /* we are using the first one, WILL ORDER BY CID IMPLICITLY
        mysql> explain SELECT cu.*, uo.time AS lastAccessTime FROM (SELECT c.*, u.sex, u.signature, u.createTime as uCreateTime, u.avatar, u.badge, u.points FROM Comment AS c LEFT JOIN User AS u ON c.uid = u.uid WHERE c.nid = 1 limit 3) as cu LEFT JOIN UserOnline AS uo ON cu.uid = uo.uid;
        +----+-------------+------------+--------+---------------+---------+---------+------------+-------+-------------+
        | id | select_type | table      | type   | possible_keys | key     | key_len | ref        | rows  | Extra       |
        +----+-------------+------------+--------+---------------+---------+---------+------------+-------+-------------+
        |  1 | PRIMARY     | <derived2> | ALL    | NULL          | NULL    | NULL    | NULL       |     3 |             |
        |  1 | PRIMARY     | uo         | ref    | uid           | uid     | 5       | cu.uid     |     1 |             |
        |  2 | DERIVED     | c          | ALL    | NULL          | NULL    | NULL    | NULL       | 11785 | Using where |
        |  2 | DERIVED     | u          | eq_ref | PRIMARY       | PRIMARY | 4       | hbbs.c.uid |     1 |             |
        +----+-------------+------------+--------+---------------+---------+---------+------------+-------+-------------+
        4 rows in set (0.00 sec)

        mysql> explain SELECT c.*, cu.* from comments AS c LEFT JOIN (SELECT u.uid, u.username, u.sex, u.signature, u.createTime, u.avatar, u.badge, u.points, uo.time AS lastAccessTime FROM User AS u LEFT JOIN UserOnline AS uo ON u.uid = uo.uid) as cu on c.uid = cu.uid WHERE c.nid = 1 limit 3;
        +----+-------------+------------+------+---------------+------+---------+------------+-------+-------------+
        | id | select_type | table      | type | possible_keys | key  | key_len | ref        | rows  | Extra       |
        +----+-------------+------------+------+---------------+------+---------+------------+-------+-------------+
        |  1 | PRIMARY     | c          | ALL  | NULL          | NULL | NULL    | NULL       | 11785 | Using where |
        |  1 | PRIMARY     | <derived2> | ALL  | NULL          | NULL | NULL    | NULL       |  3023 |             |
        |  2 | DERIVED     | u          | ALL  | NULL          | NULL | NULL    | NULL       |  3023 |             |
        |  2 | DERIVED     | uo         | ref  | uid           | uid  | 5       | hbbs.u.uid |     1 |             |
        +----+-------------+------------+------+---------------+------+---------+------------+-------+-------------+
        4 rows in set (0.05 sec)
       */

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      $sql = 'SELECT c.*,'
            . ' u.username, u.sex, u.signature, u.createTime as joinTime, u.lastAccessIPInt as accessIP, u.avatar, u.badge, u.points'
            . ' FROM Comment AS c LEFT JOIN User AS u ON c.uid = u.uid'
            . ' WHERE c.nid = ' . $nid . ' ORDER BY c.createTime ASC ' . $limit . ' ' . $offset;
      $arr = $this->_db->select( $sql );

      foreach ( $arr as $i => $r )
      {
         $arr[$i]['files'] = $this->_db->select( 'SELECT fid, name, path FROM Image WHERE cid = ' . $r['cid'] . ' ORDER BY fid ASC' );
      }

      return $arr;
   }

   public function getYellowPageNodeList( $tid, $limit = FALSE, $offset = FALSE )
   {
      if ( \is_array( $tid ) )
      {
         $where = 'IN (' . \implode( ',', $tid ) . ')';
      }
      else
      {
         $where = '= ' . (int) $tid;
      }

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      $sql = 'SELECT n.title, n.viewCount, yp.*,'
            . ' (SELECT COUNT(*) FROM Comment AS c WHERE c.nid = n.nid) AS commentCount,'
            . ' IFNULL((SELECT c.createTime FROM Comment AS c WHERE c.nid = n.nid ORDER BY c.createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            . ' FROM Node AS n JOIN NodeYellowPage AS yp ON n.nid = yp.nid'
            . ' WHERE n.status > 0 AND n.tid ' . $where
            . ' ORDER BY n.weight DESC, lastUpdateTime DESC ' . $limit . ' ' . $offset;

      $list = $this->_db->select( $sql );

      foreach ( $list as $i => $n )
      {
         $sql = 'SELECT avg(rating) AS ratingAvg, count(*) AS ratingCount FROM YPRating WHERE nid = ' . $n['nid'];
         $list[$i] = \array_merge( $n, $this->_db->row( $sql ) );
      }

      return $list;
   }

   public function getYellowPageNode( $nid )
   {
      $sql = 'SELECT n.title, n.viewCount, n.body, yp.*, r.ratingAvg, r.ratingCount,'
            . ' (SELECT COUNT(*) FROM Comment AS c WHERE c.nid = n.nid) AS commentCount,'
            . ' IFNULL((SELECT c.createTime FROM Comment AS c WHERE c.nid = n.nid ORDER BY c.createTime DESC LIMIT 1), n.createTime) AS lastUpdateTime'
            . ' FROM Node AS n JOIN NodeYellowPage AS yp ON n.nid = yp.nid,'
            . ' (SELECT avg(rating) AS ratingAvg, count(*) AS ratingCount FROM YPRating WHERE nid = ' . $nid . ') AS r'
            . ' WHERE n.status > 0 AND n.nid = ' . $nid;

      $arr = $this->_db->row( $sql );

      if ( \sizeof( $arr ) > 0 )
      {
         $arr['files'] = $this->_db->select( 'SELECT fid, name, path FROM Image WHERE cid IS NULL AND nid = ' . $nid . ' ORDER BY fid ASC' );
         return $arr;
      }
      else
      {
         return NULL;
      }
   }

   public function getYellowPageNodeComments( $nid, $limit = false, $offset = false )
   {
      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      $sql = 'SELECT c.*, u.username,'
            . ' (SELECT rating FROM YPRating WHERE nid = c.nid AND uid = c.uid) AS rating'
            . ' FROM Comment AS c JOIN User AS u ON c.uid = u.uid'
            . ' WHERE c.nid = ' . $nid . ' ORDER BY c.createTime ASC ' . $limit . ' ' . $offset;
      return $this->_db->select( $sql );
   }

   /**
    * get tag tree for a node
    * @staticvar array $tags
    * @param type $nid
    * @return type
    */
   public function getTags( $nid )
   {
      static $tags = array( );

      if ( !\array_key_exists( $nid, $tags ) )
      {
         $sql = 'SELECT t.* FROM Tag AS t JOIN Node AS n ON n.tid = t.tid WHERE n.nid = ' . (int) $nid;
         $tag = $this->_db->row( $sql );

         if ( !empty( $tag ) )
         {
            if ( $tag['tid'] == $tag['root'] ) // root tag
            {
               $tags[$nid] = array( $tag );
            }
            elseif ( $tag['parent'] == $tag['root'] ) // 1 level child
            {
               $root = $this->_db->row( 'SELECT t.* FROM Tag AS t WHERE t.tid = ' . $tag['root'] );
               $tags[$nid] = array( $root, $tag );
            }
            else // 2 level child
            {
               $arr = $this->_db->select( 'SELECT t.* FROM Tag AS t WHERE t.tid IN (' . $tag['parent'] . ', ' . $tag['root'] . ')' );
               foreach ( $arr as $t )
               {
                  if ( $t['tid'] == $tag['root'] )
                  {
                     $root = $t;
                  }
                  else
                  {
                     $parent = $t;
                  }
               }
               $tags[$nid] = array( $root, $parent, $tag );
            }
         }
      }
      return $tags[$nid];
   }

   public function getLatestForumTopics()
   {
      $sql = 'SELECT nid, title, createTime FROM Node WHERE tid IN (' . implode( ',', Tag::getLeafTIDs( 1 ) ) . ') AND status = 1 ORDER BY createTime DESC LIMIT 15';
      return $this->_db->select( $sql );
   }

   public function getHotForumTopics( $timestamp )
   {
      $sql = 'SELECT n.nid, n.title, (SELECT count(*) FROM Comment AS c WHERE c.nid = n.nid) AS commentCount '
            . 'FROM Node AS n WHERE n.createTime > ' . $timestamp . ' AND n.tid IN (' . implode( ',', Tag::getLeafTIDs( 1 ) ) . ') AND n.status = 1 ORDER BY commentCount DESC LIMIT 15';
      return $this->_db->select( $sql );
   }

   public function getHotForumTopicNIDs( $timestamp )
   {
      $nids = array( );
      foreach ( $this->getHotForumTopics( $timestamp ) as $t )
      {
         $nids[] = $t['nid'];
      }
      return $nids;
   }

   public function getLatestYellowPages()
   {
      $sql = 'SELECT nid, title, createTime FROM Node WHERE tid IN (' . implode( ',', Tag::getLeafTIDs( 2 ) ) . ') AND status = 1 ORDER BY createTime DESC LIMIT 15';
      return $this->_db->select( $sql );
   }

   public function getLatestImmigrationPosts()
   {
      $sql = 'SELECT nid, title, createTime FROM Node WHERE tid = 15 AND status = 1 ORDER BY createTime DESC LIMIT 13';
      return $this->_db->select( $sql );
   }

   public function getLatestForumTopicReplies()
   {
      $sql = 'SELECT c.nid, max(c.cid) AS lastCID, n.title, (SELECT count(*) FROM Comment AS c1 WHERE c1.nid = c.nid) AS commentCount '
            . 'FROM Comment AS c JOIN Node AS n ON c.nid = n.nid WHERE n.tid IN (' . implode( ',', Tag::getLeafTIDs( 1 ) ) . ') AND n.status = 1 GROUP BY c.nid ORDER BY lastCID DESC LIMIT 15';
      $arr = $this->_db->select( $sql );
      // YING
      $found = array( );
      foreach ( $arr as $i )
      {
         $k = \array_search( $i['nid'], $found );
         if ( $k !== FALSE )
         {
            unset( $found[$k] );
         }
      }
      $n = \sizeof( $found );
      if ( $n > 0 )
      {
         $arr2 = $this->_db->select( 'SELECT c.nid, max(c.cid) AS lastCID, n.title, (SELECT count(*) FROM Comment AS c1 WHERE c1.nid = c.nid) AS commentCount FROM Comment AS c JOIN Node AS n ON c.nid = n.nid WHERE n.nid IN (' . \implode( ', ', $found ) . ')' );

         $n = \sizeof( $arr2 );
         for ( $i = 0; $i < $n; $i++ )
         {
            $r = \mt_rand( 7, 14 );
            $arr[$r] = $arr2[$i];
         }
      }
      return $arr;
   }

   public function getLatestYellowPageReplies()
   {
      $sql = 'SELECT c.nid, max(c.cid) AS lastCID, n.title, (SELECT count(*) FROM Comment AS c1 WHERE c1.nid = c.nid) AS commentCount '
            . 'FROM Comment AS c JOIN Node AS n ON c.nid = n.nid WHERE n.tid IN (' . implode( ',', Tag::getLeafTIDs( 2 ) ) . ') AND n.status = 1 GROUP BY c.nid ORDER BY lastCID DESC LIMIT 15';
      return $this->_db->select( $sql );
   }

   public function getNodeCount( $tid )
   {
      if ( \is_array( $tid ) )
      {
         $where = 'IN (' . \implode( ',', $tid ) . ')';
      }
      else
      {
         $where = '= ' . (int) $tid;
      }

      return $this->_db->val( 'SELECT count(*) FROM Node WHERE tid ' . $where );
   }

   public function getNodeStat()
   {
      $today = \strtotime( \date( "m/d/Y" ) );
      $sql = 'SELECT'
            . ' (SELECT count(*) FROM Node) AS nodeCount,'
            . ' (SELECT count(*) FROM Comment) AS commentCount,'
            . ' (SELECT count(*) FROM Node WHERE status = 1 AND createTime >= ' . $today . ' ) as nodeTodayCount,'
            . ' (SELECT count(*) FROM Comment WHERE createTime >= ' . $today . ' ) as commentTodayCount';
      $r = $this->_db->row( $sql );
      $r['postCount'] = $r['nodeCount'] + $r['commentCount'];
      unset( $r['commentCount'] );

      return $r;
   }

   public function updateRating( $nid, $uid, $rating, $time )
   {
      $this->_db->query( 'REPLACE INTO YPRating (nid,uid,rating,time) VALUES (' . $nid . ',' . $uid . ',' . $rating . ',' . $time . ')' );
   }

   public function deleteRating( $nid, $uid )
   {
      $this->_db->query( 'DELETE FROM YPRating WHERE nid = ' . $nid . ' AND uid = ' . $uid );
   }

   /**
    * @param \lzx\core\Request $request
    */
   public function validatePostContent( $request )
   {
      return TRUE;

      //$request->uid;
      $nodeCount = $this->_db->val( 'SELECT COUNT(*) FROM Node WHERE uid = ' . $request->uid );
      if ( $nodeCount > 3 )
      {
         return TRUE;
      }

      $geo = \geoip_record_by_name( $request->ip );
      if ( $geo['country_code'] == 'US' )
      {
         return TRUE;
      }

      // check against previous 2 nodes
      if ( $nodeCount > 1 )
      {
         $arr = $this->_db->query( 'SELECT title, body FROM Node WHERE uid = ' . $request->uid . 'ORDER BY nid DESC LIMIT 2' );
         foreach ( $arr as $data )
         {
            // check title
            // check body

            if ( FALSE )
            {
               return FALSE;
            }
         }
      }
      // check against previous 2 comments
      $arr = $this->_db->query( 'SELECT body FROM Comment WHERE uid = ' . $request->uid . 'ORDER BY cid DESC LIMIT 2' );
      foreach ( $arr as $body )
      {
         // check body
         if ( FALSE )
         {
            return FALSE;
         }
      }

      // check against example spam posts (body only)
      $words = $this->_db->query( 'SELECT word FROM SpamWord' );
      foreach ( $words as $w )
      {
         if ( FALSE )
         {
            return FALSE;
         }
      }

      return TRUE;
   }

}

//__END_OF_FILE__
