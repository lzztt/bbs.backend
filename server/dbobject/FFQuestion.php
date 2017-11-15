<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * @property $id
 * @property $aid
 * @property $body
 */
class FFQuestion extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_questions';
        $fields = [
            'id' => 'id',
            'aid' => 'aid',
            'body' => 'body'
        ];

        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
