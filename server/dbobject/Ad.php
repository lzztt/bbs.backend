<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Ad extends DBObject
{
    public $id;
    public $name;
    public $typeId;
    public $expTime;
    public $email;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'ads', $id, $properties);
    }
}
