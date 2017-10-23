<?php

namespace lzx\db;

use lzx\db\DBException;

/**
 * Description of DB
 *
 * @author ikki
 */
abstract class DBStatement
{
    private $param;

    abstract public function execute();

    abstract public function insert_id();

    abstract public function affected_rows();
}

//__END_OF_FILE__
