<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Session extends DBObject
{
    public $id;
    public $data;
    public $atime;
    public $uid;
    public $cid;
    public $crc;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'sessions', $id, $properties);
    }
}
