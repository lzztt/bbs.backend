<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class Image extends DBObject
{
    public $id;
    public $nid;
    public $cid;
    public $name;
    public $path;
    public $height;
    public $width;
    public $cityId;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'images', $id, $properties);
    }
}
