<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class User extends DBObject
{
    public $id;
    public $username;
    public $password;
    public $email;
    public $wechat;
    public $qq;
    public $website;
    public $firstname;
    public $lastname;
    public $sex;
    public $birthday;
    public $location;
    public $occupation;
    public $interests;
    public $favoriteQuotation;
    public $relationship;
    public $signature;
    public $createTime;
    public $lastAccessTime;
    public $lastAccessIp;
    public $status;
    public $timezone;
    public $avatar;
    public $type;
    public $role;
    public $badge;
    public $points;
    public $cid;

    public function __construct($id = null, string $properties = '')
    {
        parent::__construct(DB::getInstance(), 'users', $id, $properties);
    }
}
