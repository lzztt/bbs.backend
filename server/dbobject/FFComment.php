<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $aid
 * @property $name
 * @property $body
 * @property $time
 */
class FFComment extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_comments';
        $fields = [
            'id' => 'id',
            'aid' => 'aid',
            'name' => 'name',
            'body' => 'body',
            'time' => 'time'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
