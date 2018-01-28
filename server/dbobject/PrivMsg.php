<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class PrivMsg extends DBObject
{
    public $id;
    public $fromUid;
    public $toUid;
    public $msgId;
    public $time;
    public $body;
    public $fromStatus;
    public $toStatus;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'priv_msgs', $id, $properties);
    }
}
