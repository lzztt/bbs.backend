<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class Activity extends DBObject
{
    public $nid;
    public $startTime;
    public $endTime;
    public $status;

    public function __construct($id = null, $properties = '')
    {
         $db = DB::getInstance();
         $table = 'activities';
         parent::__construct($db, $table, $id, $properties);
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
