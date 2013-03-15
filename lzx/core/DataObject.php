<?php

/**
 * @package lzx\core\DataObject
 */

namespace lzx\core;

use lzx\core\MySQL;

/*
 * first field is the id field, must be integer
 */

/**
 * @property \lzx\core\MySQL $_db
 */

abstract class DataObject
{

   private static $_int_type_key = array('int', 'bool');
   private static $_float_type_key = array('float', 'double', 'real');
   private static $_text_type_key = array('char', 'text', 'binary', 'blob', 'date', 'time', 'year');
   private static $_fields = array();
   protected $_db;
   protected $_table;
   protected $_fields_all;
   protected $_fields_int;
   protected $_fields_float;
   protected $_fields_text;
   private $_exists;
   private $_join_tables;
   private $_join_fields;
   private $_where;
   private $_order;

   /*
    * user input fields will not have alias
    */

   public function __construct(MySQL $db, $table, $load_id = NULL, $fields = '')
   {
      if (!\array_key_exists($table, self::$_fields))
      {
         self::$_fields[$table] = $this->_getFieldType($table, $db);
      }
      $this->_fields_all = self::$_fields[$table]['all'];
      $this->_fields_int = self::$_fields[$table]['int'];
      $this->_fields_float = self::$_fields[$table]['float'];
      $this->_fields_text = self::$_fields[$table]['text'];

      $this->_db = $db;
      $this->_table = $table;

      $this->_exists = FALSE;
      $this->_join_tables = '';
      $this->_join_fields = '';
      $this->_where = array();
      $this->_order = array();

      $pkey = $this->_get_pkey();

      if ($load_id) // not empty
      {
         $this->id = $load_id;
         $this->load($fields);
      }
   }

   /**
    * This prevents trying to set fields which don't exist
    */
   public function __set($key, $val)
   {
      if ($key == 'id')
      {
         $val_int = (int) $val;
         if ($val_int > 0)
         {
            $pkey = $this->_get_pkey();
            $this->$pkey = $val_int;
         }
         else
         {
            throw new \Exception('Non-integer ID : ' . $val);
         }
      }
      elseif (\in_array($key, $this->_fields_all))
      {
         $this->$key = $val;
      }
      else
      {
         throw new \Exception('ERROR set key : ' . $key);
      }
   }

   /**
    * this is a shortcut so you can
    *  do like $company->id instead of company->companyID
    */
   public function __get($key)
   {
      if ($key == 'id')
      {
         $pkey = $this->_get_pkey();
         return $this->$pkey;
      }
      elseif (\in_array($key, $this->_fields_all)) // fix unset properties
      {
         return NULL;
      }
      else
      {
         throw new \Exception('ERROR get key : ' . $key);
      }
   }

   public function __isset($key)
   {
      if ($key == 'id')
      {
         $pkey = $this->_get_pkey();
         return isset($this->$pkey);
      }
      elseif (\in_array($key, $this->_fields_all)) // fix unset properties
      {
         return FALSE;
      }
      else
      {
         throw new \Exception('ERROR isset key : ' . $key);
      }
   }

   public function __unset($key)
   {
      if ($key == 'id')
      {
         $pkey = $this->_get_pkey();
         unset($this->$pkey);
      }
      elseif (!\in_array($key, $this->_fields_all))
      {
         throw new \Exception('ERROR unset key : ' . $key);
      }
   }

   public function getFields()
   {
      return $this->_fields_all;
   }

   /**
    * Loads values to instance from DB
    * will not return NULL field!
    *
    * user input fields will not have alias
    *
    * @param string $fields
    * @param boolean $clearConditions
    */
   public function load($fields = '', $clearConditions = TRUE)
   {
      if ($clearConditions)
      {
         $this->clearConditions();
      }

      $arr = $this->getList($fields, 1);

      if (sizeof($arr) == 1)
      {
         foreach ($arr[0] as $key => $val)
         {
            $this->$key = $val;
         }

         $this->_clean(); // remove NULL value

         $this->_exists = TRUE;
      }
      else
      {
         $this->_exists = FALSE;
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
      $id_int = \intval($this->id);
      if ($id_int > 0)
      {
         $pkey = $this->_get_pkey();
         $return_status = $this->_db->query('DELETE FROM ' . $this->_table . ' WHERE ' . $pkey . ' = ' . $id_int);

         $this->_exists = FALSE;
         return ($return_status !== FALSE);
      }
      else
      {
         throw new \Exception('Non-integer ID : ' . $this->id);
      }
   }

   /**
    * Determines Add or Update operation
    *
    * could not save NULL value
    * use setNULL()
    *
    * @return bool
    */
   public function save()
   {
      if (isset($this->id))
      {
         $this->where('id', $this->id, '=');
         return $this->update();
      }

      return $this->add();
   }

   /**
    * Insert a record
    *
    * @return bool
    */
   public function add()
   {
      $this->_clean(); // clean data types

      $values = '';
      $fields = '';

      foreach ($this->_fields_all as $key)
      {
         if (isset($this->$key)) // will not save NULL value
         {
            $fields .= $key . ', ';
            $values .= (\is_string($this->$key) ? $this->_db->str($this->$key) : $this->$key ) . ', ';
         }
      }

      $fields = \substr($fields, 0, -2);
      $values = \substr($values, 0, -2);

      if ($values) // not empty
      {
         $sql = 'INSERT '
            . 'INTO ' . $this->_table . ' (' . $fields . ') '
            . 'VALUES (' . $values . ')';

         $return_status = $this->_db->query($sql);

         if ($return_status === FALSE || $this->_db->affected_rows() != 1)
         {
            return FALSE;
         }

         if (!isset($this->id))
         {
            $this->id = $this->_db->insert_id();
         }

         $this->_exists = TRUE;
         return TRUE;
      }
      else
      {
         throw new \Exception('can not save empty object to database');
      }
   }

   /**
    * Update a record
    *
    * user input fields will not have alias
    *
    * @return bool
    */
   public function update($fields = '')
   {
      $this->_clean();

      if (\sizeof($this->_where) == 0)
      {
         if (isset($this->id))
         {
            $this->where('id', $this->id, '=');
         }
         else
         {
            throw new \Exception('no where condition set. will not update the whole table');
         }
      }

      $pkey = $this->_get_pkey();
      $fields = empty($fields) ? \array_diff($this->_fields_all, array($pkey)) : \array_intersect($this->_fields_all, \explode(',', $fields));

      if (\in_array($pkey, $fields))
      {
         throw new \Exception('could not update primary key : ' . $pkey);
      }

      $values = '';

      foreach ($fields as $key)
      {
         if (isset($this->$key)) // will not save NULL value
         {
            $values .= $key . '=' . (\is_string($this->$key) ? $this->_db->str($this->$key) : $this->$key) . ', ';
         }
      }

      $values = \substr($values, 0, -2);

      if ($values) // not empty
      {
         $sql = 'UPDATE ' . $this->_table . ' '
            . 'SET ' . $values . ' '
            . 'WHERE ' . implode(' AND ', $this->_where);

         $return_status = $this->_db->query($sql);

         return ($return_status !== FALSE);
      }
      else
      {
         throw new \Exception('no values to update, may use setNULL($fields) to update to NULL');
      }
   }

   /*
    * user input fields will not have alias
    * will set value in database to NULL
    */

   public function setNULL($fields)
   {
      if (\strlen($fields) == 0)
      {
         throw new \Exception('fields string is empty : ' . $fields);
      }

      $field_array = array_intersect($this->_fields_all, \explode(',', $fields));
      // does not make sense to set all field to NULL by default
      if (\sizeof($field_array) == 0)
      {
         throw new \Exception('fields string does not contain any valid field name : ' . $fields);
      }

      $pkey = $this->_get_pkey();
      if (\in_array($pkey, $field_array))
      {
         throw new \Exception('could not set primary key to NULL : ' . $pkey);
      }

      // id is not a safe integer without $this->_clean() called
      if (isset($this->id))
      {
         $id = (int) $this->id;
         if ($id > 0)
         {
            $this->where('id', $id, '=');
            $this->id = $id;
         }
         else
         {
            throw new \Exception('Non-integer ID : ' . $this->id);
         }
      }
      else
      {
         if (\sizeof($this->_where) == 0)
         {
            throw new \Exception('no where condition set. will not update the whole table');
         }
      }

      $values = '';
      foreach ($field_array as $key)
      {
         $values .= $key . '= NULL, ';
      }
      $values = \substr($values, 0, -2); // will not be empty, at least one field

      $sql = 'UPDATE ' . $this->_table . ' '
         . 'SET ' . $values . ' '
         . 'WHERE ' . \implode(' AND ', $this->_where);

      $return_status = $this->_db->query($sql);
      return ($return_status !== FALSE);
   }

   public function clearConditions()
   {
      $this->_exists = FALSE;
      $this->_join_tables = '';
      $this->_join_fields = '';
      $this->_where = array();
      $this->_order = array();
   }

   public function getCount()
   {
      $this->_clean(); // remove NULL field, set NULL filter through where() methord

      $this->_set_where(); // automatically add a filter for values we already have

      return $this->_select('count(*)');
   }

   /*
    * user input fields may have alias 'AS'
    *
    * return array with the primary key as index
    */

   public function getIndexedList($fields = '', $limit = false, $offset = false)
   {
      $list = array();
      $pkey = $this->_get_pkey();

      foreach ($this->getList($fields, $limit, $offset) as $i)
      {
         $list[$i[$pkey]] = $i;
      }
      return $list;
   }

   /**
    * Selects from DB, returns array
    *
    * user input fields may have alias
    * will get id field anyway!
    *
    * @param integer $limit
    * @param integer $offset
    * @return array
    */
   public function getList($fields = '', $limit = false, $offset = false)
   {
      $this->_clean(); // remove NULL field, set NULL filter through where() methord

      $this->_set_where(); // automatically add a filter for values we already have

      if (empty($fields))
      {
         $fields = $this->_table . '.*';
      }
      else
      {
         $fields = $this->_field_sql_string($fields, $this->_table);
      }

      return $this->_select($fields, $limit, $offset);
   }

   /*
    * select query
    * user input fields may have alias
    */

   private function _field_sql_string($fields, $table)
   {
      $fields_array = \explode(',', $fields);
      $fields = '';

      if ($table == $this->_table)
      {
         $pkey = $this->_get_pkey();
         $pkey_in_fields = FALSE;

         foreach ($fields_array as $field)
         {
            $field = \explode(' ', \trim($field));
            if (\in_array($field[0], $this->_fields_all))
            {
               if ($field[0] == $pkey)
               {
                  $pkey_in_fields = TRUE;
               }
            }
            else
            {
               throw new \Exception('ERROR non-existing field : ' . $field);
            }
            $as = (\sizeof($field) > 1 && $field[1]) ? ' AS ' . $field[1] : '';
            $fields .= $table . '.' . $field[0] . $as . ', ';
         }

         if (!$pkey_in_fields)
         {
            $fields = $table . '.' . $pkey . ', ' . $fields;
         }
      }
      else
      {
         foreach ($fields_array as $field)
         {
            $field = \explode(' ', trim($field));

            $as = (\sizeof($field) > 1 && $field[1]) ? ' AS ' . $field[1] : '';
            $fields .= $table . '.' . $field[0] . $as . ', ';
         }
      }

      return \substr($fields, 0, -2);
   }

   /**
    * Adds a join to the getList() query
    *
    * user input fields may have alias
    *
    * @param string $table name of foreign table to join with
    * @param string $foreign_key name of local field which holds primary key of foreign table
    * @param string $fields one or more fields to select from the foreign table delimited by commas
    * @param string $pkey primary key of foreign table
    * @param string $jointype
    */
   function join($table, $join_key, $fields, $jointype = 'LEFT')
   {
      static $join_id = 0;

      $join_key = \explode('=', $join_key);
      if (!\in_array($join_key[0], $this->_fields_all))
      {
         throw new \Exception('first joined field does not exist in current table : ', $join_key[0]);
      }

      if (\sizeof($join_key) < 2)
      {
         $join_key[1] = $join_key[0];
      }

      $arr = \explode(' ', trim($table));
      if (\strlen($arr[0]) == 0)
      {
         throw new \Exception('empty table name to join : ', $table);
      }

      $join_id++;

      $table = $arr[0];
      $t_alias = (\sizeof($arr) > 1 && $arr[1]) ? $arr[1] : 'join' . $join_id;

      $this->_join_fields.= ', ' . $this->_field_sql_string($fields, $t_alias);

      $this->_join_tables.= ' ' . $jointype . ' JOIN ' . $table . ' AS ' . $t_alias . ' '
         . 'ON ' . $this->_table . '.' . $join_key[0] . ' = ' . $t_alias . '.' . $join_key[1];
   }

   /**
    * Adds a SQL conditional
    *
    * only set where condition for current table fields
    * user input fields will not alias
    * Example:
    * id = 1
    *
    * @param string $sql
    */
   public function where($field, $value, $condition)
   {
      if ($field === 'id')
      {
         $field = $this->_get_pkey();
      }

      if (\in_array($field, $this->_fields_all))
      {
         //could also set "field IS (NOT) NULL" condition, when $value = null
         if ($value === NULL)
         {
            $value = 'NULL';
            $condition = \in_array($condition, array('=', 'is', 'IS')) ? 'IS' : 'IS NOT';
         }
         elseif (\is_string($value) || \is_numeric($value))
         {
            if (\in_array($field, $this->_fields_int))
            {
               $value = (int) $value;
            }
            elseif (\in_array($field, $this->_fields_float))
            {
               $value = (float) $value;
            }
            else
            {
               $value = $this->_db->str($value);
            }
         }
         elseif (\is_array($value))
         {
            if (\sizeof($value) == 0)
            {
               throw new \Exception('empty value set provided in where condition');
            }

            $value_clean = array();
            if (\in_array($field, $this->_fields_int))
            {
               foreach ($value as $v)
               {
                  $value_clean[] = (int) $v;
               }
            }
            elseif (\in_array($field, $this->_fields_float))
            {
               foreach ($value as $v)
               {
                  $value_clean[] = (float) $v;
               }
            }
            else
            {
               foreach ($value as $v)
               {
                  $value_clean[] = $this->_db->str($v);
               }
            }

            $value = '(' . \implode(', ', $value_clean) . ')';
            $condition = \in_array($condition, array('=', 'in', 'IN')) ? 'IN' : 'NOT IN';
         }
         else
         {
            throw new \Exception('ERROR wrong value type : ' . gettype($value));
         }

         $this->_where[$field . '_' . $condition] = '(' . $this->_table . '.' . $field . ' ' . $condition . ' ' . $value . ')';
      }
      else
      {
         throw new \Exception('ERROR non-existing field : ' . $field);
      }
   }

   /**
    * Adds an SQL ORDER BY
    *
    * only order by current table's fields
    * user input fields will not have alias
    *
    * @param $field name of field with optional desc\asc seperated by one space
    */
   public function order($field, $order = 'ASC')  //ASC or DESC
   {
      $order = \strtoupper($order);
      if (!\in_array($order, array('ASC', 'DESC')))
      {
         throw new \Exception('wrong order : ' . $order);
      }

      if (\in_array($field, $this->_fields_all))
      {
         $this->_order[$field] = $this->_table . '.' . $field . ' ' . $order;
      }
      else
      {
         throw new \Exception('ERROR non-existing field : ' . $field);
      }
   }

   private function _set_where()
   {
      foreach ($this->_fields_all as $key)
      {
         // automatically add a filter for values we already have
         if (isset($this->$key))  // ignore NULL values
         {
            $this->where($key, $this->$key, '=');
         }
      }
   }

   private function _get_pkey()
   {
// DO NOT USE static TO CACHE, IT WILL MIX WITH DIFFERENT classes
      return $this->_fields_all[0];
   }

   private function _clean()
   {
      foreach ($this->_fields_int as $field)
      {
         if (isset($this->$field)) // isset and not NULL
         {
            $this->$field = (int) $this->$field;
         }
      }

      if (isset($this->id) && $this->id <= 0)
      {
         throw new \Exception('Non-integer ID : ' . $this->id);
      }

      foreach ($this->_fields_float as $field)
      {
         if (isset($this->$field))
         {
            $this->$field = (float) $this->$field;
         }
      }

      foreach ($this->_fields_text as $field)
      {
         if (isset($this->$field))
         {
            $this->$field = (string) $this->$field;
         }
      }
   }

   private function _select($fields = '', $limit = FALSE, $offset = FALSE)
   {
      $where = '';
      $order = '';

      if (\sizeof($this->_where) > 0)
      {
         $where = 'WHERE ' . \implode(' AND ', $this->_where);
      }

      if (\sizeof($this->_order) > 0)
      {
         $order = 'ORDER BY ' . \implode(', ', $this->_order);
      }

      $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
      $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

      $sql = 'SELECT ' . $fields . $this->_join_fields . ' '
         . 'FROM ' . $this->_table . $this->_join_tables . ' '
         . $where . ' '
         . $order . ' '
         . $limit . ' '
         . $offset;

      return ($fields == 'count(*)') ? $this->_db->val($sql) : $this->_db->select($sql);
   }

   private function _getFieldType($table, MySQL $db)
   {
      $int_fields = array();
      $float_fields = array();
      $text_fields = array();
      $res = $db->select('DESCRIBE ' . $table);
      foreach ($res as $r)
      {
         $found = FALSE;
         foreach (self::$_int_type_key as $i)
         {
            if (\strpos($r['Type'], $i) !== FALSE)
            {
               $int_fields[] = $r['Field'];
               $found = TRUE;
               break;
            }
         }
         if ($found)
         {
            continue;
         }
         foreach (self::$_float_type_key as $f)
         {
            if (\strpos($r['Type'], $f) !== FALSE)
            {
               $float_fields[] = $r['Field'];
               $found = TRUE;
               break;
            }
         }
         if ($found)
         {
            continue;
         }
         foreach (self::$_text_type_key as $t)
         {
            if (\strpos($r['Type'], $t) !== FALSE)
            {
               $text_fields[] = $r['Field'];
               $found = TRUE;
               break;
            }
         }
         if ($found)
         {
            continue;
         }
         throw new \Exception('could not determine field type : ' . $r['Field'] . ' -> ' . $r['Type']);
      }

      return array(
         'all' => \array_merge($int_fields, $float_fields, $text_fields),
         'int' => $int_fields,
         'float' => $float_fields,
         'text' => $text_fields
      );
   }

}

//__END_OF_FILE__