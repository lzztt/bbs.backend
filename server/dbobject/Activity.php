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
    public function __construct($id = null, $properties = '')
    {
         $db = DB::getInstance();
         $table = 'activities';
         $fields = [
               'nid' => 'nid',
               'startTime' => 'start_time',
               'endTime' => 'end_time',
               'status' => 'status'
         ];
         parent::__construct($db, $table, $fields, $id, $properties);
    }

    public function getRecentActivities($count, $now)
    {
         return $this->call('get_recent_activities(' . $now . ',' . $count . ')');
    }

    public function getActivityList($limit = 25, $offset = 0)
    {
         return $this->call('get_activities(' . $limit . ',' . $offset . ')');
    }

    public function addActivity($nid, $beginTime, $endTime)
    {
         $this->call('add_activity(' . $nid . ',' . $beginTime . ',' . $endTime . ')');
    }
}
