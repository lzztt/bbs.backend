<?php declare(strict_types=1);

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
}
