<?php declare(strict_types=1);

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
 * @property $tidForum
 * @property $tidYp
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
            'tidForum' => 'tid_forum',
            'tidYp' => 'tid_yp'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
