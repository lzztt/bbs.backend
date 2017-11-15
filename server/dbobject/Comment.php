<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $nid
 * @property $uid
 * @property $tid
 * @property $body
 * @property $hash
 * @property $createTime
 * @property $lastModifiedTime
 */
class Comment extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'comments';
        $fields = [
            'id' => 'id',
            'nid' => 'nid',
            'uid' => 'uid',
            'tid' => 'tid',
            'body' => 'body',
//            'hash' => 'hash',
            'createTime' => 'create_time',
            'lastModifiedTime' => 'last_modified_time'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
