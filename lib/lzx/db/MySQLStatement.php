<?php

namespace lzx\db;

use lzx\db\DBStatement;

/**
 * @param mysqli $db
 */
class MySQLStatement extends DBStatement
{

    private $result = NULL;
    private $statement;
    private $params;
    private $values;

    public function __construct( \mysqli_stmt $statement, array $params )
    {
        $this->statement = $statement;
        $this->params = $params;
    }
    
    public function bind_param( array $params )
    {
        foreach($params as $k => $v)
        {
            if(  \in_array( $k, $this->params ))
            {
                if(\is_int())
                $this->values[$k] = $v;
            }
        }
    }

    public function execute()
    {
        return $this->statement->execute();
    }

    public function insert_id()
    {
        return $this->statement->insert_id;
    }

    public function affected_rows()
    {
        return $this->statement->affected_rows;
    }

}

//__END_OF_FILE__