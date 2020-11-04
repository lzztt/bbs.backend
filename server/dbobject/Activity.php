<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Activity extends DBObject
{
     public $nid;
     public $startTime;
     public $endTime;
     public $status;

     public function __construct($id = null, string $properties = '')
     {
          parent::__construct(DB::getInstance(), 'activities', $id, $properties);
     }

     public function getRecentActivities(int $count, int $now): array
     {
          return $this->call('get_recent_activities(' . $now . ',' . $count . ')');
     }

     public function getActivityList(int $limit = 25, int $offset = 0): array
     {
          return $this->call('get_activities(' . $limit . ',' . $offset . ')');
     }

     public function addActivity(int $nid, int $beginTime, int $endTime): void
     {
          $this->call('add_activity(' . $nid . ',' . $beginTime . ',' . $endTime . ')');
     }
}
