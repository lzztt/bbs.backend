<?php

namespace lzx\db;

class DBException extends \Exception
{
    protected $db;
    protected $sql;

    public function __construct($message = null, $db = null, $sql = null, $code = 11, $previous = null)
    {
        $message = '[DB] ' . ($db ? $db : 'NULL')
                . ' [SQL] ' . ($sql ? $sql : 'NULL')
                . ' [MSG] ' . ($message ? $message : 'NULL');
        parent::__construct($message, $code, $previous);
    }
}

//__END_OF_FILE__
