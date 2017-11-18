<?php declare(strict_types=1);

/**
 * @package lzx\core\DataObject
 */

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

/**
 * @property $adId
 * @property $nid
 * @property $address
 * @property $phone
 * @property $fax
 * @property $email
 * @property $website
 */
class NodeYellowPage extends DBObject
{
    public $nid;
    public $adId;
    public $address;
    public $phone;
    public $fax;
    public $email;
    public $website;

    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'node_yellowpages';
        parent::__construct($db, $table, $id, $properties);
    }
}
