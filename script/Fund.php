<?php declare(strict_types=1);

namespace script;

use lzx\db\DBObject;
use lzx\db\DB;

class Fund extends DBObject
{
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'funds';
        $fields = [
            'id' => 'id',
            'symbol' => 'symbol',
            'name' => 'name',
            'fid' => 'fid',
            'cid' => 'cid'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }

    public function __toString()
    {
        return $this->symbol . ' : ' . $this->name;
    }
}
