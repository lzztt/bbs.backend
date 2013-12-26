<?php

namespace lzx\db;

use lzx\db\DB;

/**
 * @param mysqli $db
 */
class MySQL extends DB
{

    private $result = NULL;

    public function __construct( $tag, $host, $user, $password, $dbname )
    {
        if ( self::$instances[$tag] === __CLASS__ )
        {
            $this->db = new \mysqli( $host, $user, $password, $dbname );

            if ( $this->db->connect_error )
            {
                $this->dbError( 'Could not connect to database: ' . $this->db->connect_error );
            }

            if ( $this->db->autocommit( FALSE ) === FALSE )
            {
                $this->dbError( 'Failed to set autocommit: ' . $this->db->error );
            }

            if ( $this->db->set_charset( 'utf8' ) === FALSE )
            {
                $this->dbError( 'Could not set default character set: ' . $this->db->error );
            }
        }
        else
        {
            $this->dbError( 'constructor cannot be called directly' );
        }
    }

    public function __destruct()
    {
        if ( $this->hasError == FALSE )
        {
            if ( $this->db->commit() === FALSE )
            {
                $this->dbError( 'Failed commit: ' . $this->error );
            }
        }
        else
        {
            if ( $this->db->rollback() === FALSE )
            {
                $this->dbError( 'Failed rollback: ' . $this->error );
            }
        }
        $this->db->autocommit( TRUE );
        // do not close! otherwise session handler won't work!
        //$this->close();
    }

    /**
     * Returns full result (assoc array) from given query
     *
     * @param string $sql
     * @return array()
     */
    public function select( $sql )
    {
        if ( \strtoupper( \substr( $sql, 0, 6 ) ) === 'SELECT' )
        {
            $this->query( $sql );

            $res = $this->db->store_result();
            $arr = array();
            foreach ( $res as $r )
            {
                $arr[] = $r;
            }

            $res->free();

            return $arr;
        }
        else
        {
            throw new \Exception( 'wrong sql type' );
        }
    }

    /**
     * 
     * @param type $sql 
     * @return boolean
     * @throws Exception
     */
    public function insert( $sql )
    {
        if ( \strtoupper( \substr( $sql, 0, 6 ) ) === 'INSERT' )
        {
            return $this->query( $sql );
        }
        else
        {
            throw new \Exception( 'wrong sql type' );
        }
    }

    /**
     * 
     * @param type $sql
     * @return boolean
     * @throws Exception
     */
    public function update( $sql )
    {
        if ( \strtoupper( \substr( $sql, 0, 6 ) ) === 'UPDATE' )
        {
            return $this->query( $sql );
        }
        else
        {
            throw new \Exception( 'wrong sql type' );
        }
    }

    public function delete( $sql )
    {
        if ( \strtoupper( \substr( $sql, 0, 6 ) ) === 'DELETE' )
        {
            return $this->query( $sql );
        }
        else
        {
            throw new \Exception( 'wrong sql type' );
        }
    }

    public function call( $proc )
    {
        $status = $this->query( 'CALL ' . $proc );

        if ( $status === FALSE )
        {
            $this->dbError( $this->db->error );
        }

        if ( $this->db->field_count )
        {
            $res = $this->db->store_result();
            $arr = array();
            foreach ( $res as $r )
            {
                $arr[] = $r;
            }
            $res->free();
            // clean additional result from stored procedure
            $this->db->next_result();
            return $arr;
        }
        else
        {
            $this->db->next_result();
            $return = $status;
        }
    }

    public function describe( $table )
    {
        $this->query( 'DESCRIBE ' . $this->db->real_escape_string( $table ) );

        $res = $this->db->store_result();

        $arr = array();
        foreach ( $res as $r )
        {
            $arr[] = $r;
        }

        $res->free();

        return $arr;
    }

    /**
     * Returns result resource from given query
     *
     * @param string $sql
     * @return MySQLi_Result
     */
    private function query( $sql )
    {
        if ( $this->debugMode )
        {
            // query debug timer and info
            $_timer = \microtime( TRUE );
            $status = $this->db->real_query( $sql );
            self::$queries[] = \sprintf( '%8.6f', \microtime( TRUE ) - $_timer ) . ' : ' . $sql;
        }
        else
        {
            $status = $this->db->real_query( $sql );
        }

        if ( $status === FALSE )
        {
            $this->dbError( 'Query error: ' . $this->db->error, $sql );
        }

        return $status;
    }

    public function affected_rows()
    {
        return $this->db->affected_rows;
    }

    public function insert_id()
    {
        return $this->db->insert_id;
    }

    /**
     * Returns a single row from given query
     *
     * @param string $sql
     * @return array()
     */
    public function row( $sql )
    {
        $rows = $this->select( $sql );

        $row = [];
        if ( \sizeof( $rows ) > 0 )
        {
            $row = $rows[0];
        }

        return $row;
    }

    /**
     * Returns a single value from given query
     *
     * @param string $sql
     * @return string result value or NULL
     */
    public function val( $sql )
    {
        $values = \array_values( $this->row( $sql ) );

        $val = NULL;
        if ( \sizeof( values ) > 0 )
        {
            $val = $values[0];
        }

        return $val;
    }

    public function escape( $str )
    {
        if ( !\is_array( $str ) )
        {
            return $this->db->real_escape_string( $str );
        }
        else
        {
            $arr = array();
            foreach ( $str as $k => $v )
            {
                $arr[$k] = $this->escape( $v );
            }
            return $arr;
        }
    }

    public function str( $str )
    {
        return '"' . $this->db->real_escape_string( $str ) . '"';
    }

}

//__END_OF_FILE__