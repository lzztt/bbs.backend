<?php

namespace lzx\core;

class Config
{

    /**
     * sections, holds the config sections
     *
     * @var array
     */
    private static $_loaded = array();

    // disable public initialization
    private function __construct()
    {
        
    }

    /**
     * Return the Session object
     *
     * @return Config
     */
    public static function getInstance( $config_file = NULL )
    {
        static $instance;

        if ( !isset( $instance ) )
        {
            $instance = new self();
        }

        if ( isset( $config_file ) )
        {
            $instance->load( $config_file );
        }

        return $instance;
    }

    private static function _arrayToObj( $array )
    {
        foreach ( $array as $key => $value )
        {
            if ( \is_array( $value ) )
            {
                $array[$key] = self::_arrayToObj( $value );
            }
            if ( \is_object( $value ) )
            {
                throw new \ErrorException( 'values for $config keys could not be an object' );
            }
        }

# Typecast to (object) will automatically convert array -> stdClass
        return (object) $array;
    }

    private static function _mergeObjs( $obj1, $obj2 )
    {
        $keys1 = \array_keys( \get_object_vars( $obj1 ) );
        $keys2 = \array_keys( \get_object_vars( $obj2 ) );
        //var_dump($keys1, $keys2);
        foreach ( $keys2 as $k )
        {
            // $k is new
            if ( !\in_array( $k, $keys1 ) )
            {
                $obj1->$k = $obj2->$k;
            }
            // old $k is simple value or numerical array
            if ( !\is_object( $obj1->$k ) )
            {
                if ( !\is_object( $obj2->$k ) )
                {
                    $obj1->$k = $obj2->$k;
                }
                else
                {
                    throw new \ErrorException( 'type mismatch: mergeing a complex value to a simple value, for key: ' . $k );
                }
            }
            // old $k is an stdClass object value
            else
            {
                if ( !\is_object( $obj2->$k ) )
                {
                    throw new \ErrorException( 'type mismatch: mergeing a simple value to a complex value, for key: ' . $k );
                    $obj1->$k = $obj2->$k;
                }
                else
                {
                    $obj1->$k = self::_mergeObjs( $obj1->$k, $obj2->$k );
                }
            }
        }

        // merge to sub keys
        if ( \get_class( $obj1 ) === 'stdClass' )
        {
            return $obj1;
        }
        // merge to $this
        else
        {
            return TRUE;
        }
    }

    public function load( $config_file )
    {
        if ( !\in_array( $config_file, self::$_loaded ) )
        {
            require_once $config_file;
            if ( isset( $config ) )
            {
                if ( \is_array( $config ) )
                {
                    foreach ( $config as $k => $v )
                    {
                        $this->$k = $v;
                    }
                }
                else
                {
                    throw new \ErrorException( '$config need to be an array' );
                }
            }
            else
            {
                throw new \ErrorException( 'file configuration requires an array variable $config' );
            }
            self::$_loaded[] = $config_file;
        }
    }

}
