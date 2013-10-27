<?php

/**
 * @package lzx\core\DataObject
 */

namespace lzx\core;

use lzx\core\MySQL;

/*
 * support tables with one primary key
 */

/**
 * @property \lzx\core\MySQL $_db
 */
abstract class DataObject
{

   protected $_db;
   protected $_table;
   private $_keys_all = array( );
   private $_keys_int = array( );
   private $_keys_float = array( );
   private $_keys_text = array( );
   private $_keys_dirty = array( );
   private $_pkey = NULL;
   private $_values = array( );
   private $_exists = FALSE;
   private $_join_tables = '';
   private $_join_keys = '';
   private $_where = array( );
   private $_order = array( );

   /*
    * user input keys will not have alias
    */

   public function __construct( MySQL $db, $table, $pkey = NULL, $keys = '' )
   {
      $this->_db = $db;
      $this->_table = $table;
      $this->_getObjectKeys();

      if ( $pkey ) // not empty
      {
         $this->_values[$this->_pkey] = $pkey;
         $this->load( $keys );
      }
   }

   /**
    * This prevents trying to set keys which don't exist
    */
   public function __set( $key, $val )
   {
      if ( \in_array( $key, $this->_keys_all ) )
      {
         $this->_values[$key] = $val;
         // mark key as dirty
         if ( !\in_array( $key, $this->_keys_dirty ) )
         {
            $this->_keys_dirty[] = $key;
         }
      }
      else
      {
         throw new \Exception( 'ERROR set key : ' . $key );
      }
   }

   public function __get( $key )
   {
      if ( \in_array( $key, $this->_keys_all ) )
      {
         return $this->_values[$key];
      }
      else
      {
         throw new \Exception( 'ERROR get key : ' . $key );
      }
   }

   public function __isset( $key )
   {
      return \array_key_exists( $key, $this->_values );
   }

   public function __unset( $key )
   {
      unset( $this->_values[$key] );

      $this->_unDirty( $key );
   }

   public function getKeys()
   {
      return $this->_keys_all;
   }

   private function _isDirty( $key )
   {
      return \in_array( $key, $this->_keys_dirty );
   }

   private function _unDirty( $key )
   {
      $dirty = \array_search( $key, $this->_keys_dirty );
      if ( $dirty !== FALSE )
      {
         unset( $this->_keys_dirty[$dirty] );
      }
   }

   /**
    * Loads values to instance from DB
    *
    * user input keys will not have alias
    *
    * @param string $keys
    */
   public function load( $keys = '' )
   {
      $this->_exists = FALSE;
      $this->_join_tables = '';
      $this->_join_keys = '';
      $this->_where = array( );
      $this->_order = array( );

      $n = 1;
      $arr = $this->getList( $keys, $n );

      if ( \sizeof( $arr ) == $n )
      {
         foreach ( $arr[0] as $key => $val )
         {
            $this->_values[$key] = $val;
         }

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
      if ( \array_key_exists( $this->_pkey, $this->_values ) )
      {
         $return_status = $this->_db->query( 'DELETE FROM ' . $this->_table . ' WHERE ' . $this->_pkey . ' = ' . $this->_values[$this->_pkey] );

         $this->_exists = FALSE;
         return ($return_status !== FALSE);
      }
      else
      {
         throw new \Exception( 'ERROR delete: invalid primary key value: [' . $this->_pkey . ':' . $this->_values[$this->_pkey] . ']' );
      }
   }

   /**
    * Insert a record
    *
    * @return bool
    */
   public function add()
   {
      if ( \sizeof( $this->_keys_dirty ) == 0 )
      {
         throw new \Exception( 'adding an object with no dirty properties to database' );
      }

      $this->_clean(); // clean data types

      $keys = '';
      $values = '';

      foreach ( $this->_keys_dirty as $key )
      {
         $keys .= $key . ', ';
         $values .= (\is_null( $this->_values[$key] ) ? 'NULL' : $this->_values[$key]) . ', ';
      }

      $keys = \substr( $keys, 0, -2 );
      $values = \substr( $values, 0, -2 );

      $sql = 'INSERT '
            . 'INTO ' . $this->_table . ' (' . $keys . ') '
            . 'VALUES (' . $values . ')';

      $return_status = $this->_db->query( $sql );

      if ( $return_status === FALSE || $this->_db->affected_rows() != 1 )
      {
         return FALSE;
      }

      if ( !\array_key_exists( $this->_pkey, $this->_values ) )
      {
         $this->_values[$this->_pkey] = $this->_db->insert_id();
      }

      $this->_keys_dirty = array( );
      $this->_exists = TRUE;
      return TRUE;
   }

   /**
    * Update a record / records
    *
    * user input keys will not have alias
    *
    * @return bool
    */
   public function update( $keys = '' )
   {
      if ( \sizeof( $this->_keys_dirty ) == 0 )
      {
         throw new \Exception( 'updating an object with no dirty properties to database' );
      }

      $this->_unDirty( $this->_pkey );

      $keys = empty( $keys ) ? $this->_keys_dirty : \array_intersect( $this->_keys_dirty, \explode( ',', $keys ) );

      if ( \sizeof( $keys ) == 0 )
      {
         throw new \Exception( 'updating key set is empty' );
      }

      if ( \in_array( $this->_pkey, $keys ) )
      {
         throw new \Exception( 'could not update primary key : ' . $this->_pkey );
      }

      $this->_clean();

      if ( \sizeof( $this->_where ) == 0 )
      {
         if ( \array_key_exists( $this->_pkey, $this->_values ) )
         {
            $this->where( $this->_pkey, $this->_values[$this->_pkey], '=' );
         }
         else
         {
            throw new \Exception( 'no where condition set. will not update the whole table' );
         }
      }

      $values = '';

      foreach ( $keys as $key )
      {
         $values .= ($key . '=' . (\is_null( $this->_values[$key] ) ? 'NULL' : $this->_values[$key]) . ', ');
      }

      $values = \substr( $values, 0, -2 );

      $sql = 'UPDATE ' . $this->_table . ' '
            . 'SET ' . $values . ' '
            . 'WHERE ' . \implode( ' AND ', $this->_where );

      $return_status = $this->_db->query( $sql );

      // unmake dirty keys for single object
      if ( \array_key_exists( $this->_pkey, $this->_values ) && $return_status !== FALSE )
      {
         $this->_keys_dirty = \array_diff( $this->_keys_dirty, $keys );
      }

      return ($return_status !== FALSE);
   }

   public function getCount()
   {
      $this->_set_where(); // automatically add a filter for values we already have

      return $this->_select( 'count(*)' );
   }

   /*
    * user input keys may have alias 'AS'
    *
    * return array with the primary key as index
    */

   public function getIndexedList( $keys = '', $limit = false, $offset = false )
   {
      $list = array( );

      foreach ( $this->getList( $keys, $limit, $offset ) as $i )
      {
         $list[$i[$this->_pkey]] = $i;
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
   public function getList( $keys = '', $limit = false, $offset = false )
   {
      $this->_set_where(); // automatically add a filter for values we already have

      if ( empty( $keys ) )
      {
         $keys = $this->_table . '.*';
      }
      else
      {
         $keys = $this->_key_sql_string( $keys, $this->_table );
      }

      return $this->_select( $keys, $limit, $offset );
   }

   /*
    * select query
    * user input keys may have alias
    */

   private function _key_sql_string( $keys, $table )
   {
      $keys_array = \explode( ',', $keys );
      $keys = '';

      if ( $table == $this->_table )
      {
         $pkey_in_keys = FALSE;

         foreach ( $keys_array as $key )
         {
            $key = \explode( ' ', \trim( $key ) );
            if ( \in_array( $key[0], $this->_keys_all ) )
            {
               if ( $key[0] == $this->_pkey )
               {
                  $pkey_in_keys = TRUE;
               }
            }
            else
            {
               throw new \Exception( 'ERROR non-existing key : ' . $key );
            }
            $as = (\sizeof( $key ) > 1 && $key[1]) ? ' AS ' . $key[1] : '';
            $keys .= $table . '.' . $key[0] . $as . ', ';
         }

         if ( !$pkey_in_keys )
         {
            $keys = $table . '.' . $this->_pkey . ', ' . $keys;
         }
      }
      else
      {
         foreach ( $keys_array as $key )
         {
            $key = \explode( ' ', trim( $key ) );

            $as = (\sizeof( $key ) > 1 && $key[1]) ? ' AS ' . $key[1] : '';
            $keys .= $table . '.' . $key[0] . $as . ', ';
         }
      }

      return \substr( $keys, 0, -2 );
   }

   /**
    * Adds a join to the getList() query
    *
    * user input keys may have alias
    *
    * @param string $table name of foreign table to join with
    * @param string $foreign_key name of local key which holds primary key of foreign table
    * @param string $keys one or more keys to select from the foreign table delimited by commas
    * @param string $pkey primary key of foreign table
    * @param string $jointype
    */
   function join( $table, $join_key, $keys, $jointype = 'LEFT' )
   {
      static $join_id = 0;

      $join_key = \explode( '=', $join_key );
      if ( !\in_array( $join_key[0], $this->_keys_all ) )
      {
         throw new \Exception( 'first joined key does not exist in current table : ', $join_key[0] );
      }

      if ( \sizeof( $join_key ) < 2 )
      {
         $join_key[1] = $join_key[0];
      }

      $arr = \explode( ' ', trim( $table ) );
      if ( \strlen( $arr[0] ) == 0 )
      {
         throw new \Exception( 'empty table name to join : ', $table );
      }

      $join_id++;

      $table = $arr[0];
      $t_alias = (\sizeof( $arr ) > 1 && $arr[1]) ? $arr[1] : 'join' . $join_id;

      $this->_join_keys.= ', ' . $this->_key_sql_string( $keys, $t_alias );

      $this->_join_tables.= ' ' . $jointype . ' JOIN ' . $table . ' AS ' . $t_alias . ' '
            . 'ON ' . $this->_table . '.' . $join_key[0] . ' = ' . $t_alias . '.' . $join_key[1];
   }

   /**
    * Adds a SQL conditional
    *
    * only set where condition for current table keys
    * user input keys will not alias
    * Example:
    * id = 1
    *
    * @param string $sql
    */
   public function where( $key, $value, $condition )
   {
      if ( !\in_array( $key, $this->_keys_all ) )
      {
         throw new \Exception( 'ERROR non-existing key : ' . $key );
      }
      // NULL value
      if ( $value === NULL )
      {
         $value = 'NULL';
         $condition = \in_array( $condition, array( '=', 'is', 'IS' ) ) ? 'IS' : 'IS NOT';
      }
      // single value
      elseif ( \is_string( $value ) || \is_numeric( $value ) )
      {
         if ( \in_array( $key, $this->_keys_int ) )
         {
            $value = \intval( $value );
         }
         elseif ( \in_array( $key, $this->_keys_float ) )
         {
            $value = \floatval( $value );
         }
         elseif ( \in_array( $key, $this->_keys_text ) )
         {
            $value = $this->_db->str( \strval( $value ) );
         }
         else
         {
            throw new \Exception( 'ERROR key : ' . $key . ' doesn\'t have a valid type' );
         }
      }
      // a list of values
      elseif ( \is_array( $value ) )
      {
         if ( \sizeof( $value ) == 0 )
         {
            throw new \Exception( 'empty value set provided in where condition' );
         }

         if ( \in_array( NULL, $value ) )
         {
            throw new \Exception( 'NULL provided in the value set. but NULL is not a value' );
         }

         $value_clean = array( );
         if ( \in_array( $key, $this->_keys_int ) )
         {
            foreach ( $value as $v )
            {
               $value_clean[] = \intval( $v );
            }
         }
         elseif ( \in_array( $key, $this->_keys_float ) )
         {
            foreach ( $value as $v )
            {
               $value_clean[] = \floatval( $v );
            }
         }
         elseif ( \in_array( $key, $this->_keys_text ) )
         {
            foreach ( $value as $v )
            {
               $value_clean[] = $this->_db->str( \strval( $v ) );
            }
         }
         else
         {
            throw new \Exception( 'ERROR key : ' . $key . ' doesn\'t have a valid type' );
         }

         $value = '(' . \implode( ', ', $value_clean ) . ')';
         $condition = \in_array( $condition, array( '=', 'in', 'IN' ) ) ? 'IN' : 'NOT IN';
      }
      else
      {
         throw new \Exception( 'ERROR wrong value type : ' . \gettype( $value ) );
      }

      $this->_where[$key . '_' . $condition] = '(' . $this->_table . '.' . $key . ' ' . $condition . ' ' . $value . ')';
   }

   /**
    * Adds an SQL ORDER BY
    *
    * only order by current table's keys
    * user input keys will not have alias
    *
    * @param $key name of key with optional desc\asc seperated by one space
    */
   public function order( $key, $order = 'ASC' )  //ASC or DESC
   {
      $order = \strtoupper( $order );
      if ( !\in_array( $order, array( 'ASC', 'DESC' ) ) )
      {
         throw new \Exception( 'wrong order : ' . $order );
      }

      if ( \in_array( $key, $this->_keys_all ) )
      {
         $this->_order[$key] = $this->_table . '.' . $key . ' ' . $order;
      }
      else
      {
         throw new \Exception( 'ERROR non-existing key : ' . $key );
      }
   }

   private function _set_where()
   {
      // automatically add a filter for values we already have
      foreach ( \array_keys( $this->_values ) as $key )
      {
         $this->where( $key, $this->_values[$key], '=' );
      }
   }

   private function _clean()
   {
      foreach ( \array_keys( $this->_values ) as $key )
      {
         if ( \is_null( $this->_values[$key] ) )
         {
            continue;
         }
         elseif ( \in_array( $key, $this->_keys_int ) )
         {
            $this->_values[$key] = \intval( $this->_values[$key] );
            continue;
         }
         elseif ( \in_array( $key, $this->_keys_float ) )
         {
            $this->_values[$key] = \floatval( $this->_values[$key] );
            continue;
         }
         $this->_values[$key] = $this->_db->str( \strval( $this->_values[$key] ) );
      }
   }

   private function _select( $keys = '', $limit = FALSE, $offset = FALSE )
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

      $sql = 'SELECT ' . $keys . $this->_join_keys . ' '
            . 'FROM ' . $this->_table . $this->_join_tables . ' '
            . $where . ' '
            . $order . ' '
            . $limit . ' '
            . $offset;

      return ($keys == 'count(*)') ? $this->_db->val( $sql ) : $this->_db->select( $sql );
   }

   private function _getObjectKeys()
   {

      static $_int_type_key = array( 'int', 'bool' );
      static $_float_type_key = array( 'float', 'double', 'real' );
      static $_text_type_key = array( 'char', 'text', 'binary', 'blob', 'date', 'time', 'year' );
      static $_keys = array( );

      if ( \array_key_exists( $this->_table, $_keys ) )
      {
         $this->_keys_all = $_keys[$this->_table]['all'];
         $this->_keys_int = $_keys[$this->_table]['int'];
         $this->_keys_float = $_keys[$this->_table]['float'];
         $this->_keys_text = $_keys[$this->_table]['text'];
         $this->_pkey = $_keys[$this->_table]['pkey'];
      }
      else
      {
         $res = $this->_db->select( 'DESCRIBE ' . $this->_table );

         foreach ( $res as $r )
         {
            $this->_keys_all[] = $r['Field'];

            // primary key
            if ( $r['Key'] == 'PRI' )
            {
               // no primary key found yet
               if ( \is_null( $this->_pkey ) )
               {
                  // int type
                  if ( \strpos( $r['Type'], 'int' ) !== FALSE )
                  {
                     $this->_pkey = $r['Field'];
                  }
               }
               else
               {
                  throw new \Exception( 'found multiple primary keys : ' . $this->_pkey . ', ' . $r['Field'] );
               }
            }

            $found = FALSE;
            foreach ( $_int_type_key as $i )
            {
               if ( \strpos( $r['Type'], $i ) !== FALSE )
               {
                  $this->_keys_int[] = $r['Field'];
                  $found = TRUE;
                  break;
               }
            }
            if ( $found )
            {
               continue;
            }
            foreach ( $_float_type_key as $f )
            {
               if ( \strpos( $r['Type'], $f ) !== FALSE )
               {
                  $this->_keys_float[] = $r['Field'];
                  $found = TRUE;
                  break;
               }
            }
            if ( $found )
            {
               continue;
            }
            foreach ( $_text_type_key as $t )
            {
               if ( \strpos( $r['Type'], $t ) !== FALSE )
               {
                  $this->_keys_text[] = $r['Field'];
                  $found = TRUE;
                  break;
               }
            }
            if ( $found )
            {
               continue;
            }
            throw new \Exception( 'could not determine key type : ' . $r['Field'] . ' -> ' . $r['Type'] );
         }

         if ( \is_null( $this->_pkey ) )
         {
            throw new \Exception( 'no primary key found: ' . $table );
         }

         $keys[$this->_table] = array(
            'all' => $this->_keys_all,
            'int' => $this->_keys_int,
            'float' => $this->_keys_float,
            'text' => $this->_keys_text,
            'pkey' => $this->_pkey
         );
      }
   }

}

//__END_OF_FILE__