<?php

namespace lzx\db;

use lzx\db\DBException;

/**
 * Description of DB
 *
 * @author ikki
 */
abstract class DB
{

    const DEFAULT_TAG = 'DEFAULT';

    public static $queries = array();
    protected static $instances = array();
    public $debugMode = FALSE;
    protected $hasError = FALSE;
    protected $db;

    protected function dbError( $msg, $sql = NULL )
    {
        $this->hasError = TRUE;
        throw new DBException( $msg, $sql );
    }

    // Singleton methord for each database
    public static final function getInstance( $tag = self::DEFAULT_TAG, array $config = array() )
    {
        if ( \count( $config ) == 0 && \array_key_exists( $tag, self::$instances ) )
        {
            return self::$instances[$tag];
        }

        // check caller class
        $dbclass = \get_called_class();
        if ( __CLASS__ === $dbclass )
        {
            throw new \Exception( $tag . ' does not exist. can not create a new instance from abstruct class (DB)' );
        }

        // check config keys
        $required_config_keys = array('host', 'user', 'password', 'dbname');
        foreach ( $required_config_keys as $key )
        {
            if ( !\array_key_exists( $key, $config ) )
            {
                throw new \InvalidArgumentException( 'missing database parameters : ' . $key );
            }
        }

        // initialize tag for new instance
        self::$instances[$tag] = $dbclass;
        $instance = new $dbclass( $tag, $config['host'], $config['user'], $config['password'], $config['dbname'] );
        // save to tag
        self::$instances[$tag] = $instance;

        return $instance;
    }

    abstract public function insert( $sql );

    abstract public function update( $sql );

    abstract public function delete( $sql );

    abstract public function call( $proc );

    abstract public function describe( $table );

    /**
     * Returns full result (assoc array) from given query
     *
     * @param string $sql
     * @return array()
     */
    abstract public function select( $sql );

    /**
     * Returns a single row from given query
     *
     * @param string $sql
     * @return array()
     */
    abstract public function row( $sql );

    /**
     * Returns a single value from given query
     *
     * @param string $sql
     * @return string result value or NULL
     */
    abstract public function val( $sql );

    abstract public function insert_id();

    abstract public function affected_rows();

    abstract public function str( $str );

}

//__END_OF_FILE__
