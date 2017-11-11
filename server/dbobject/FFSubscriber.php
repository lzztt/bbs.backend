<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $email
 * @property $time
 */
class FFSubscriber extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_subscribers';
        $fields = [
            'id' => 'id',
            'email' => 'email',
            'time' => 'time'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
