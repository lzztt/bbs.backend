<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class NodeYellowPage extends DBObject
{
    public $nid;
    public $adId;
    public $address;
    public $phone;
    public $fax;
    public $email;
    public $website;

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'node_yellowpages';
        parent::__construct($db, $table, $id, $properties);
    }
}
