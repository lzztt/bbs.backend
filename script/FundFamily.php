<?php declare(strict_types=1);

namespace script;

use lzx\db\DBObject;
use lzx\db\DB;

/**
 * Description of FundFamily
 *
 * @property $id
 * @property $name
 */
class FundFamily extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'fund_families';
        $fields = [
            'id' => 'id',
            'name' => 'name'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
