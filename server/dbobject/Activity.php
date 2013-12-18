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

   public function __construct( $id = null, $fields = '' )
   {
      $db = DB::getInstance();
      $table = 'activities';
      $feilds = [
         'nid' => 'nid',
         'startTime' => 'start_time',
         'endTime' => 'end_time',
         'status' => 'status'
      ];
      parent::__construct( $db, $table, $feilds, $id, $fields );
   }

   public function getRecentActivities( $count, $now )
   {
      $sql = 'SELECT a.nid, a.startTime, a.endTime, n.title, IF(a.startTime < ' . $now . ', "now", "future") as class FROM activities AS a JOIN nodes AS n ON a.nid = n.nid WHERE a.status = 1 AND a.endTime > ' . $now . ' ORDER BY a.startTime LIMIT ' . $count;
      $activities = $this->_db->select( $sql );

      $count_old = $count - sizeof( $activities );
      if ( $count_old > 0 )
      {
         $sql = 'SELECT a.nid, a.startTime, a.endTime, n.title, "old" as class FROM activities AS a JOIN nodes AS n ON a.nid = n.nid WHERE a.status = 1 AND a.endTime < ' . $now . ' ORDER BY a.startTime DESC LIMIT ' . $count_old;
         $arr_old = $this->_db->select( $sql );
         $activities = \array_merge( $activities, $arr_old );
      }

      return $activities;
   }

   public function getActivityList( $limit = NULL, $offset = NULL )
   {
      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';
      $sql = 'SELECT a.nid, a.startTime, a.endTime, n.title, n.viewCount FROM activities AS a JOIN nodes AS n ON a.nid = n.nid WHERE a.status = 1 ORDER BY a.startTime DESC ' . $limit . ' ' . $offset;
      return $this->_db->select( $sql );
   }

   public function addActivity( $nid, $startTime, $endTime )
   {
      $this->_db->query( 'REPLACE INTO activities (nid,startTime,endTime) VALUES (' . $nid . ',' . $startTime . ',' . $endTime . ')' );
   }

}

//__END_OF_FILE__
