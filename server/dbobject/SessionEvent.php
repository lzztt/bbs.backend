<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class SessionEvent extends DBObject
{
    public $id;
    public $userId;
    public $time;
    public $ip;
    public $agent;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'session_events', $id, $properties);
    }
}
