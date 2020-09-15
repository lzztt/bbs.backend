<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class SessionEvent extends DBObject
{
    public const EVENT_BEGIN = 'begin';
    public const EVENT_UPDATE = 'update';
    public const EVENT_END = 'end';

    public $id;
    public $sessionId;
    public $userId;
    public $time;
    public $event;
    public $ip;
    public $agent;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'session_events', $id, $properties);
    }
}
