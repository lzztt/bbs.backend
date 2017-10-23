<?php

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $name
 * @property $uriName
 * @property $ForumRootID
 * @property $YPRootID
 */
class City extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'cities';
        $fields = [
            'id' => 'id',
            'name' => 'name',
            'uriName' => 'uri_name',
            'ForumRootID' => 'tid_forum',
            'YPRootID' => 'tid_yp'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}

//__END_OF_FILE__
