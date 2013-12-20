<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * two level category system
 * @property $aid
 * @property $startTime
 * @property $endTime
 * @property $status
 */
class Activity extends DBObject
{

   public function __construct( $id = null, $properties = '' )
   {
      $db = DB::getInstance();
      $table = 'activities';
      $fields = [
         'nid' => 'nid',
         'startTime' => 'start_time',
         'endTime' => 'end_time',
         'status' => 'status'
      ];
      parent::__construct( $db, $table, $fields, $id, $properties );
   }

   public function getRecentActivities( $count, $now )
   {
      $sql = 'SELECT a.nid, a.start_time AS startTime, a.end_time AS endTime, n.title, IF(a.start_time < ' . $now . ', "now", "future") as class FROM activities AS a JOIN nodes AS n ON a.nid = n.id WHERE a.status = 1 AND a.end_time > ' . $now . ' ORDER BY a.start_time LIMIT ' . $count;
      $activities = $this->_db->select( $sql );

      $count_old = $count - sizeof( $activities );
      if ( $count_old > 0 )
      {
         $sql = 'SELECT a.nid, a.start_time AS startTime, a.end_time AS endTime, n.title, "old" as class FROM activities AS a JOIN nodes AS n ON a.nid = n.id WHERE a.status = 1 AND a.end_time < ' . $now . ' ORDER BY a.start_time DESC LIMIT ' . $count_old;
         $arr_old = $this->_db->select( $sql );
         $activities = \array_merge( $activities, $arr_old );
      }

      return $activities;
   }

   public function getActivityList( $limit = NULL, $offset = NULL )
   {
      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';
      $sql = 'SELECT a.nid, a.start_time AS startTime, a.end_time AS endTime, n.title, n.view_count AS viewCount FROM activities AS a JOIN nodes AS n ON a.nid = n.id WHERE a.status = 1 ORDER BY a.start_time DESC ' . $limit . ' ' . $offset;
      return $this->_db->select( $sql );
   }

   public function addActivity( $nid, $startTime, $endTime )
   {
      $this->_db->query( 'REPLACE INTO activities (nid,start_time,end_time) VALUES (' . $nid . ',' . $startTime . ',' . $endTime . ')' );
   }

}

//__END_OF_FILE__
