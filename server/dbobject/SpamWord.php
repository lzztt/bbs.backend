<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $code
 * @property $time
 * @property $uri
 */
class SpamWord extends DBObject
{
    public $id;
    public $word;
    public $title;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'spam_words';
        parent::__construct($db, $table, $id, $properties);
    }
}
