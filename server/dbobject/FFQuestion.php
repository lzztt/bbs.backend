<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DBObject;
use lzx\db\DB;

class FFQuestion extends DBObject
{
    public $id;
    public $aid;
    public $body;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'ff_questions';

        parent::__construct($db, $table, $id, $properties);
    }
}
