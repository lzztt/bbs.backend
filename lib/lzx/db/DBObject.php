<?php

namespace lzx\db;

/*
 * support tables with one primary key
 */

/**
 * @property \lzx\db\DB $_db
 */
abstract class DBObject
{

    const T_INT = 1;
    const T_FLOAT = 2;
    const T_STRING = 3;

    private $_properties;
    private $_properties_dirty = [];
    private $_pkey_property = NULL;
    private $_values = [];
    protected $_db;
    protected $_table;
    private $_fields;
    private $_fields_type = [];
    private $_exists = FALSE;
    private $_where = [];
    private $_bind_values = [];
    private $_order = [];

    /*
     * user input keys will not have alias
     */

    public function __construct( DB $db, $table, array $fields, $id = NULL, $properties = '' )
    {
        $this->_properties = \array_keys( $fields );

        $this->_db = $db;
        $this->_table = $table;
        $this->_fields = $fields;

        $this->_getFieldsTypeAndPKey();

        if ( $id ) // not empty
        {
            if ( $this->_pkey_property )
            {
                $this->_values[$this->_pkey_property] = $id;
                if ( !\is_null( $properties ) )
                {
                    $this->load( $properties );
                }
            }
            else
            {
                throw new \Exception( 'Table does not have primary key. Cannot load id=' . id );
            }
        }
    }

    /**
     * This prevents trying to set keys which don't exist
     */
    public function __set( $prop, $val )
    {
        if ( \in_array( $prop, $this->_properties ) )
        {
            $this->_setValue( $prop, $val );
            // mark property as dirty
            if ( !\in_array( $prop, $this->_properties_dirty ) )
            {
                $this->_properties_dirty[] = $prop;
            }
        }
        else
        {
            throw new \Exception( 'ERROR set property : ' . $prop );
        }
    }

    public function __get( $prop )
    {
        if ( \in_array( $prop, $this->_properties ) )
        {
            return $this->_values[$prop];
        }
        else
        {
            throw new \Exception( 'ERROR get property : ' . $prop );
        }
    }

    public function __isset( $prop )
    {
        return \array_key_exists( $prop, $this->_values );
    }

    public function __unset( $prop )
    {
        if ( \array_key_exists( $prop, $this->_values ) )
        {
            unset( $this->_values[$prop] );

            $this->_unDirty( $prop );
        }
    }
    
    public function __toString()
    {
       $str = '';
       foreach( $this->_values as $k => $v )
       {
          $str = $str . $k . ' -> ' . $v . PHP_EOL;
       }
       return $str;
    }

    public function getProperties()
    {
        return $this->_properties;
    }

    public function getDirtyProperties()
    {
        return $this->_properties_dirty;
    }

    private function _bind( $prop )
    {
        $this->_bind_values[':' . $this->_fields[$prop]] = $this->_values[$prop];
    }

    private function _clear( $clearData = FALSE )
    {
        $this->_where = [];
        $this->_bind_values = [];
        $this->_order = [];
        if ( $clearData )
        {
            $this->_values = [];
            $this->_properties_dirty = [];
            $this->_exists = FALSE;
        }
    }

    private function _setValue( $prop, $value )
    {
        if ( \is_null( $value ) )
        {
            $this->_values[$prop] = NULL;
        }
        else
        {
            switch ( $this->_fields_type[$this->_fields[$prop]] )
            {
                case self::T_INT:
                    $this->_values[$prop] = \intval( $value );
                    break;
                case self::T_FLOAT:
                    $this->_values[$prop] = \floatval( $value );
                    break;
                case self::T_STRING:
                    $this->_values[$prop] = \strval( $value );
                    break;
                default:
                    throw new \Exception( 'non-supported field data type: ' . $this->_fields[$prop] . '(' . $this->_fields_type[$this->_fields[$prop]] . ')' );
            }
        }
    }

    private function _isDirty( $prop )
    {
        return \in_array( $prop, $this->_properties_dirty );
    }

    private function _unDirty( $prop )
    {
        $dirty = \array_search( $prop, $this->_properties_dirty );
        if ( $dirty !== FALSE )
        {
            unset( $this->_properties_dirty[$dirty] );
        }
    }

    /**
     * Call a database procedure
     * 
     * @param type $proc
     * @param array $args
     */
    public function call( $proc, $params = [] )
    {
        return $this->_db->query( 'CALL ' . $proc, $params );
    }

    /**
     * Loads values to instance from DB
     *
     * user input keys will not have alias
     *
     * @param string $keys
     */
    public function load( $properties = '' )
    {
        $this->_exists = FALSE;
        $this->_where = [];
        $this->_order = [];

        $arr = $this->getList( $properties, 1 );

        if ( \sizeof( $arr ) == 1 )
        {
            foreach ( $arr[0] as $prop => $val )
            {
                $this->_setValue( $prop, $val );
            }

            // clean and undirty properties
            $properties = \array_keys( $arr[0] );
            $this->_properties_dirty = \array_diff( $this->_properties_dirty, $properties );

            $this->_exists = TRUE;
        }
        else
        {
            $this->_exists = FALSE;
            return FALSE;
        }
    }

    public function exists()
    {
        return $this->_exists;
    }

    /*
     * YES, DataObject will have an int primery key
     */

    public function delete()
    {
        if ( \array_key_exists( $this->_pkey_property, $this->_values ) )
        {
            $this->_bind( $this->_pkey_property );
            $status = $this->_db->query( 'DELETE FROM ' . $this->_table . ' WHERE ' . $this->_fields[$this->_pkey_property] . ' = :' . $this->_fields[$this->_pkey_property], $this->_bind_values );

            $this->_clear( TRUE );
            return ($status !== FALSE);
        }
        else
        {
            throw new \Exception( 'ERROR delete: invalid primary key value: [' . $this->_fields[$this->_pkey_property] . ' : ' . $this->_values[$this->_pkey_property] . ']' );
        }
    }

    /**
     * Insert a record
     *
     * @return bool
     */
    public function add()
    {
        if ( \sizeof( $this->_properties_dirty ) == 0 )
        {
            throw new \Exception( 'adding an object with no dirty properties to database' );
        }

        $fields = '';
        $values = '';
        foreach ( $this->_properties_dirty as $p )
        {
            $fields = $fields . $this->_fields[$p] . ', ';
            $values = $values . ':' . $this->_fields[$p] . ', ';
            $this->_bind( $p );
        }
        $fields = \substr( $fields, 0, -2 );
        $values = \substr( $values, 0, -2 );

        $sql = 'INSERT '
            . 'INTO ' . $this->_table . ' (' . $fields . ') '
            . 'VALUES (' . $values . ')';

        $this->_db->query( $sql, $this->_bind_values );

        if ( !\array_key_exists( $this->_pkey_property, $this->_values ) )
        {
            $this->_values[$this->_pkey_property] = $this->_db->insert_id();
        }

        $this->_clear();
        // undirty all properties
        $this->_properties_dirty = [];
        $this->_exists = TRUE;
        return TRUE;
    }

    /**
     * Update a record / records
     *
     * @return bool
     */
    public function update( $properties = '' )
    {
        // do not update pkey
        $this->_unDirty( $this->_pkey_property );

        if ( \sizeof( $this->_properties_dirty ) == 0 )
        {
            throw new \Exception( 'updating an object with no dirty properties to database' );
        }

        if ( empty( $properties ) )
        {
            $properties = $this->_properties_dirty;
        }
        else
        {
            $properties = \array_unique( \explode( ',', $properties ) );
            if ( \sizeof( $properties ) != \sizeof( \array_intersect( $properties, $this->_properties_dirty ) ) )
            {
                throw new \Exception( 'updating non-dirty property! updating: ' . \implode( ',', $properties ) . ' - current dirty properties: ' . \implode( ',', $this->_properties_dirty ) );
            }
        }

        if ( empty( $properties ) )
        {
            throw new \Exception( 'updating property set is empty' );
        }

        if ( empty( $this->_where ) )
        {
            if ( \array_key_exists( $this->_pkey_property, $this->_values ) )
            {
                $this->where( $this->_pkey_property, $this->_values[$this->_pkey_property], '=' );
            }
            else
            {
                throw new \Exception( 'no where condition set. will not update the whole table' );
            }
        }

        $values = '';

        foreach ( $properties as $p )
        {
            $values = $values . $this->_fields[$p] . '=:' . $this->_fields[$p] . ', ';
            $this->_bind( $p );
        }

        $values = \substr( $values, 0, -2 );

        $sql = 'UPDATE ' . $this->_table . ' '
            . 'SET ' . $values . ' '
            . 'WHERE ' . \implode( ' AND ', $this->_where );
        $status = $this->_db->query( $sql, $this->_bind_values );

        $this->_clear();
        // undirty properties
        $this->_properties_dirty = \array_diff( $this->_properties_dirty, $properties );

        return $status;
    }

    public function getCount()
    {
        $this->_set_where(); // automatically add a filter for values we already have

        return \intval( \array_pop( \array_pop( $this->_select( 'COUNT(*)' ) ) ) );
    }

    /*
     * user input keys may have alias 'AS'
     *
     * return array with the primary key as index
     */

    public function getIndexedList( $properties = '', $limit = FALSE, $offset = FALSE )
    {
        $list = [];

        foreach ( $this->getList( $keys, $limit, $offset ) as $i )
        {
            $list[$i[$this->_pkey_property]] = $i;
        }
        return $list;
    }

    /**
     * Selects from DB, returns array
     *
     * user input keys may have alias
     * will always get primary key values
     *
     * @param integer $limit
     * @param integer $offset
     * @return array
     */
    public function getList( $properties = '', $limit = FALSE, $offset = FALSE )
    {
        $this->_set_where(); // automatically add a filter for values we already have

        $fields = $this->_select_fields( $properties );

        return $this->_select( $fields, $limit, $offset );
    }

    /*
     * select query
     */

    private function _select_fields( $properties )
    {
        if ( empty( $properties ) )
        {
            $properties_array = $this->_properties;
            $fields = '';
            foreach ( $properties_array as $p )
            {
                if ( $p === $this->_fields[$p] )
                {
                    $fields = $fields . $p . ', ';
                }
                else
                {
                    $fields = $fields . $this->_fields[$p] . ' AS ' . $p . ', ';
                }
            }
        }
        else
        {
            $properties_tmp = \explode( ',', $properties );
            // add primary key property
            $properties_tmp[] = $this->_pkey_property;
            $properties_array = \array_unique( $properties_tmp );
            $fields = '';

            foreach ( $properties_array as $p )
            {
                if ( !\in_array( $p, $this->_properties ) )
                {
                    throw new \Exception( 'ERROR non-existing property : ' . $p );
                }
                if ( $p === $this->_fields[$p] )
                {
                    $fields = $fields . $p . ', ';
                }
                else
                {
                    $fields = $fields . $this->_fields[$p] . ' AS ' . $p . ', ';
                }
            }
        }

        return \substr( $fields, 0, -2 );
    }

    /**
     * Adds a condition for SQL query
     *
     * @param string $sql
     */
    public function where( $prop, $value, $condition )
    {
        if ( !\in_array( $prop, $this->_properties ) )
        {
            throw new \Exception( 'ERROR non-existing propperty : ' . $prop );
        }
        // NULL value
        if ( $value === NULL )
        {
            $value = 'NULL';
            $condition = \in_array( $condition, ['=', 'is', 'IS'] ) ? 'IS' : 'IS NOT';
        }
        else
        {
            if ( \is_array( $value ) )
            {
                // a list of values
                if ( \sizeof( $value ) == 0 )
                {
                    throw new \Exception( 'empty value set provided in where condition' );
                }

                if ( \in_array( NULL, $value ) )
                {
                    throw new \Exception( 'NULL provided in the value set. but NULL is not a value' );
                }

                $value_clean = [];
                switch ( $this->_fields_type[$this->_fields[$prop]] )
                {
                    case self::T_INT:
                        foreach ( $value as $v )
                        {
                            $value_clean[] = \intval( $v );
                        }
                        break;
                    case self::T_FLOAT:
                        foreach ( $value as $v )
                        {
                            $value_clean[] = \floatval( $v );
                        }
                        break;
                    case self::T_STRING:
                        foreach ( $value as $v )
                        {
                            $value_clean[] = $this->_db->str( $v );
                        }
                        break;
                    default:
                        throw new Exception( 'non-supported field data type: ' . $this->_fields[$prop] . '(' . $this->_fields_type[$this->_fields[$prop]] . ')' );
                }
                $value = '(' . \implode( ', ', $value_clean ) . ')';
                $condition = \in_array( $condition, ['=', 'in', 'IN'] ) ? 'IN' : 'NOT IN';
            }
            else
            {
                if ( !\in_array( $condition, ['>', '>=', '<', '<=', '=', '!=', '<>', 'LIKE', 'NOT LIKE'] ) )
                {
                    throw new Exception( 'non-supported operator: ' . $condition );
                }
                // a single value, bind
                $this->_bind_values[':' . $prop] = $value;
                $value = ':' . $prop;
            }
        }

        $this->_where[$prop . '_' . $condition] = $this->_fields[$prop] . ' ' . $condition . ' ' . $value;
    }

    /**
     * Adds an SQL ORDER BY
     *
     * only order by current table's keys
     * user input keys will not have alias
     *
     * @param $key name of key with optional desc\asc seperated by one space
     */
    public function order( $prop, $order = 'ASC' )  //ASC or DESC
    {
        $order = \strtoupper( $order );
        if ( !\in_array( $order, ['ASC', 'DESC'] ) )
        {
            throw new \Exception( 'wrong order : ' . $order );
        }

        if ( \in_array( $prop, $this->_properties ) )
        {
            $this->_order[$prop] = $this->_fields[$prop] . ' ' . $order;
        }
        else
        {
            throw new \Exception( 'ERROR non-existing property : ' . $prop );
        }
    }

    private function _set_where()
    {
        // automatically add a filter for values we already have
        foreach ( \array_keys( $this->_values ) as $prop )
        {
            $this->where( $prop, $this->_values[$prop], '=' );
        }
    }

    private function _select( $properties = '', $limit = FALSE, $offset = FALSE )
    {
        $where = '';
        $order = '';

        if ( \sizeof( $this->_where ) > 0 )
        {
            $where = 'WHERE ' . \implode( ' AND ', $this->_where );
        }

        if ( \sizeof( $this->_order ) > 0 )
        {
            $order = 'ORDER BY ' . \implode( ', ', $this->_order );
        }

        $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
        $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

        $sql = 'SELECT ' . $properties
            . ' FROM ' . $this->_table . ' '
            . $where . ' '
            . $order . ' '
            . $limit . ' '
            . $offset;

        $arr = $this->_db->query( $sql, $this->_bind_values );
        $this->_clear();

        return $arr;
    }

    private function _getFieldsTypeAndPKey()
    {
        static $_fields = [];
        static $_int_type = ['int', 'bool'];
        static $_float_type = ['float', 'double', 'real'];
        static $_text_type = ['char', 'text', 'binary', 'blob', 'date', 'time', 'year'];

        if ( \array_key_exists( $this->_table, $_fields ) )
        {
            $this->_fields_type = $_fields[$this->_table]['types'];
            $this->_pkey_property = $_fields[$this->_table]['pkey'];
        }
        else
        {
            $res = $this->_db->query( 'DESCRIBE ' . $this->_table );

            foreach ( $res as $r )
            {
                // primary key
                if ( $r['Key'] == 'PRI' )
                {
                    // no primary key found yet
                    if ( \is_null( $this->_pkey_property ) )
                    {
                        $prop = \array_search( $r['Field'], $this->_fields );
                        if ( $prop !== FALSE )
                        {
                            $this->_pkey_property = $prop;
                        }
                        else
                        {
                            throw new \Exception( 'non-property primary key found : ' . $r['Field'] );
                        }
                    }
                    else
                    {
                        throw new \Exception( 'found multiple primary keys in db table : ' . $this->_fields[$this->_pkey_property] . ', ' . $r['Field'] );
                    }
                }

                // ignore fields that not set
                if ( !\in_array( $r['Field'], $this->_fields ) )
                {
                    continue;
                }

                $found = FALSE;

                foreach ( $_int_type as $i )
                {
                    if ( \strpos( $r['Type'], $i ) !== FALSE )
                    {
                        $this->_fields_type[$r['Field']] = self::T_INT;
                        $found = TRUE;
                        break;
                    }
                }

                if ( !$found )
                {
                    foreach ( $_float_type as $f )
                    {
                        if ( \strpos( $r['Type'], $f ) !== FALSE )
                        {
                            $this->_fields_type[$r['Field']] = self::T_FLOAT;
                            $found = TRUE;
                            break;
                        }
                    }

                    if ( !$found )
                    {
                        foreach ( $_text_type as $t )
                        {
                            if ( \strpos( $r['Type'], $t ) !== FALSE )
                            {
                                $this->_fields_type[$r['Field']] = self::T_STRING;
                                $found = TRUE;
                                break;
                            }
                        }
                    }
                }

                if ( !$found )
                {
                    throw new \Exception( 'could not determine key type : ' . $r['Field'] . ' -> ' . $r['Type'] );
                }
            }

            if ( \is_null( $this->_pkey_property ) )
            {
                throw new \Exception( 'no primary key found: ' . $this->_table );
            }

            if ( \sizeof( $this->_fields_type ) < \sizeof( $this->_fields ) )
            {
                throw new \Exception( 'the following fields do not exist in db table: ' . \implode( ', ', \array_diff( \array_values( $this->_fields ), \array_keys( $this->field_types ) ) ) );
            }

            $_fields[$this->_table] = [
                'types' => $this->_fields_type,
                'pkey' => $this->_pkey_property
            ];
        }
    }

}

//__END_OF_FILE__