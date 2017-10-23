<?php

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $email
 * @property $name
 * @property $time
 * @property $cid
 */
class Email extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'emails';
        $fields = [
            'email' => 'email',
            'name' => 'name',
            'time' => 'time',
            'cid' => 'cid'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}

//__END_OF_FILE__
