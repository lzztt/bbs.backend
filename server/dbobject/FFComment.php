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
    public $id;
    public $aid;
    public $name;
    public $body;
    public $time;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_comments';
        parent::__construct($db, $table, $id, $properties);
    }
}
