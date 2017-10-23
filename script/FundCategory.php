<?php

namespace script;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * Description of FundCategory
 *
 * @property $id
 * @property $name
 */
class FundCategory extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'fund_categories';
        $fields = [
            'id' => 'id',
            'name' => 'name'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}

//__END_OF_FILE__
