<?php declare(strict_types=1);

namespace site\dbobject;

use lzx\db\DB;
use lzx\db\DBObject;

class SpamWord extends DBObject
{
    public $id;
    public $word;
    public $title;

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'spam_words';
        parent::__construct($db, $table, $id, $properties);
    }
}
