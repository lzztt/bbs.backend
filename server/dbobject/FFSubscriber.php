<?php declare(strict_types=1);

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
    public $id;
    public $email;
    public $time;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_subscribers';
        parent::__construct($db, $table, $id, $properties);
    }
}
