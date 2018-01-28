<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Tag extends DBObject
{
    public $id;
    public $name;
    public $description;
    public $parent;
    public $root;
    public $weight;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'tags', $id, $properties);
    }
}
