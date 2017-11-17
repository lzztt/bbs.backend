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
    public function __construct($id = null, $properties = '')
    {
        $db = DB::getInstance();
        $table = 'node_yellowpages';
        $fields = [
            'nid' => 'nid',
            'adId' => 'ad_id',
            'address' => 'address',
            'phone' => 'phone',
            'fax' => 'fax',
            'email' => 'email',
            'website' => 'website'
        ];
        parent::__construct($db, $table, $fields, $id, $properties);
    }
}
