<?php

declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class RentThread extends DBObject
{
    public $id;
    public $createTime;
    public $lastUpdateTime;
    public $site;
    public $tid;
    public $type;
    public $author;
    public $title;
    public $body;
    public $images;
    public $status;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'rent_threads', $id, $properties);
    }
}
