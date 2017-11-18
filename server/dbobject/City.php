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
    public $id;
    public $name;
    public $uriName;
    public $tidForum;
    public $tidYp;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'cities';
        parent::__construct($db, $table, $id, $properties);
    }
}
