<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class City extends DBObject
{
    public $id;
    public $name;
    public $uriName;
    public $tidForum;
    public $tidYp;

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'cities';
        parent::__construct($db, $table, $id, $properties);
    }
}
