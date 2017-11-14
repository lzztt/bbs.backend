<?php

namespace lzx\db;

/*
 * support tables with one primary key
 */

/**
 * @property \lzx\db\DB $db
 */
abstract class DBObject
{
    const T_INT = 1;
    const T_FLOAT = 2;
    const T_STRING = 3;

    private $properties;
    private $properties_dirty = [];
    private $pkey_property = null;
    private $values = [];
    protected $db;
    protected $table;
    private $fields;
    private $fields_type = [];
    private $exists = false;
    private $where = [];
    private $bind_values = [];
    private $order = [];

    /*
     * user input keys will not have alias
     */

    public function __construct(DB $db, $table, array $fields, $id = null, $properties = '')
    {
        $this->properties = array_keys($fields);

        $this->db = $db;
        $this->table = $table;
        $this->fields = $fields;

        $this->getFieldsTypeAndPKey();

        if ($id) { // not empty
            if (!in_array(gettype($id), ['integer', 'string'])) {
                throw new \Exception('Invalid ID type: ' . gettype($id));
            }

            if ($this->pkey_property) {
                $this->setValue($this->pkey_property, $id);
                if (!is_null($properties)) {
                    $this->load($properties);
                }
            } else {
                throw new \Exception('Table does not have primary key. Cannot load id=' . $id);
            }
        }
    }

    /**
     * This prevents trying to set keys which don't exist
     */
    public function __set($prop, $val)
    {
        if (in_array($prop, $this->properties)) {
            $this->setValue($prop, $val);
            // mark property as dirty
            if (!in_array($prop, $this->properties_dirty)) {
                $this->properties_dirty[] = $prop;
            }
        } else {
            throw new \Exception('ERROR set property : ' . $prop);
        }
    }

    public function __get($prop)
    {
        if (in_array($prop, $this->properties)) {
            return $this->values[$prop];
        } else {
            throw new \Exception('ERROR get property : ' . $prop);
        }
    }

    public function __isset($prop)
    {
        return array_key_exists($prop, $this->values);
    }

    public function __unset($prop)
    {
        if (array_key_exists($prop, $this->values)) {
            unset($this->values[$prop]);

            $this->unDirty($prop);
        }
    }

    public function __toString()
    {
        $str = '';
        foreach ($this->values as $k => $v) {
            $str = $str . $k . ' -> ' . $v . PHP_EOL;
        }
        return $str;
    }

    public function toArray()
    {
        return $this->values;
    }

    public function getProperties()
    {
        return $this->properties;
    }

    public function getDirtyProperties()
    {
        return $this->properties_dirty;
    }

    private function bind($prop)
    {
        if (array_key_exists($prop, $this->values)) {
            $this->bind_values[':' . $this->fields[$prop]] = $this->values[$prop];
        } else {
            throw new \Exception('could not find binding value for property: ' . $prop);
        }
    }

    private function clear($clearData = false)
    {
        $this->where = [];
        $this->bind_values = [];
        $this->order = [];
        if ($clearData) {
            $this->values = [];
            $this->properties_dirty = [];
            $this->exists = false;
        }
    }

    private function setValue($prop, $value)
    {
        if (is_null($value)) {
            $this->values[$prop] = null;
        } else {
            if (!in_array(gettype($value), ['integer', 'string', 'double', 'boolean'])) {
                throw new \Exception('Invalid value type: ' . $this->fields[$prop] . '(' . gettype($value) . ')');
            }

            switch ($this->fields_type[$this->fields[$prop]]) {
                case self::T_INT:
                    $this->values[$prop] = intval($value);
                    break;
                case self::T_FLOAT:
                    $this->values[$prop] = floatval($value);
                    break;
                case self::T_STRING:
                    $this->values[$prop] = strval($value);
                    break;
                default:
                    throw new \Exception('non-supported field data type: ' . $this->fields[$prop] . '(' . $this->fields_type[$this->fields[$prop]] . ')');
            }
        }
    }

    private function isDirty($prop)
    {
        return in_array($prop, $this->properties_dirty);
    }

    private function unDirty($prop)
    {
        $dirty = array_search($prop, $this->properties_dirty);
        if ($dirty !== false) {
            unset($this->properties_dirty[$dirty]);
        }
    }

    /**
     * Call a database procedure
     *
     * @param type $proc
     * @param array $args
     */
    public function call($proc, $params = [])
    {
        return $this->db->query('CALL ' . $proc, $params);
    }

    // convert array keys from column names to field names
    protected function convertFields(array $arr, array $fields)
    {
        if ($arr) {
            foreach ($fields as $name => $column) {
                if ($name != $column) {
                    if (array_key_exists($column, $arr[0])) {
                        foreach ($arr as $i => $r) {
                            $arr[$i][$name] = $r[$column];
                            unset($arr[$i][$column]);
                        }
                    } else {
                        throw new \Exception('cannot convert non-exist column: ' . $column);
                    }
                }
            }
        }
        return $arr;
    }

    /**
     * Loads values to instance from DB
     *
     * user input keys will not have alias
     *
     * @param string $keys
     */
    public function load($properties = '')
    {
        $this->exists = false;
        $this->where = [];
        $this->bind_values = [];
        $this->order = [];

        $arr = $this->getList($properties, 1);

        if (sizeof($arr) == 1) {
            foreach ($arr[0] as $prop => $val) {
                $this->setValue($prop, $val);
            }

            // clean and undirty properties
            $properties = array_keys($arr[0]);
            $this->properties_dirty = array_diff($this->properties_dirty, $properties);

            $this->exists = true;
        } else {
            $this->exists = false;
            return false;
        }
    }

    public function exists()
    {
        return $this->exists;
    }

    /*
     * YES, DataObject will have an int primery key
     */

    public function delete()
    {
        if (!$this->pkey_property) {
            throw new \Exception('Table does not have primary key. Deletion without primary key is not supported yet');
        }

        if (array_key_exists($this->pkey_property, $this->values)) {
            $this->bind($this->pkey_property);
            $status = $this->db->query('DELETE FROM ' . $this->table . ' WHERE ' . $this->fields[$this->pkey_property] . ' = :' . $this->fields[$this->pkey_property], $this->bind_values);

            $this->clear(true);
            return ($status !== false);
        } else {
            throw new \Exception('ERROR delete: invalid primary key value: [' . $this->fields[$this->pkey_property] . ' : ' . $this->values[$this->pkey_property] . ']');
        }
    }

    /**
     * Insert a record
     *
     * @return bool
     */
    public function add()
    {
        if (sizeof($this->properties_dirty) == 0) {
            throw new \Exception('adding an object with no dirty properties to database');
        }

        $fields = '';
        $values = '';
        foreach ($this->properties_dirty as $p) {
            $fields = $fields . $this->fields[$p] . ', ';
            $values = $values . ':' . $this->fields[$p] . ', ';
            $this->bind($p);
        }
        $fields = substr($fields, 0, -2);
        $values = substr($values, 0, -2);

        $sql = 'INSERT '
            . 'INTO ' . $this->table . ' (' . $fields . ') '
            . 'VALUES (' . $values . ')';

        $this->db->query($sql, $this->bind_values);

        if ($this->pkey_property && !array_key_exists($this->pkey_property, $this->values)) {
            $this->values[$this->pkey_property] = $this->db->insertId();
        }

        $this->clear();
        // undirty all properties
        $this->properties_dirty = [];
        $this->exists = true;
        return true;
    }

    /**
     * Update a record / records
     *
     * @return bool
     */
    public function update($properties = '')
    {
        // do not update pkey
        if ($this->pkey_property) {
            $this->unDirty($this->pkey_property);
        }

        if (sizeof($this->properties_dirty) == 0) {
            throw new \Exception('updating an object with no dirty properties to database');
        }

        if (empty($properties)) {
            $properties = $this->properties_dirty;
        } else {
            $properties = array_unique(explode(',', $properties));
            if (sizeof($properties) != sizeof(array_intersect($properties, $this->properties_dirty))) {
                throw new \Exception('updating non-dirty property! updating: ' . implode(',', $properties) . ' - current dirty properties: ' . implode(',', $this->properties_dirty));
            }
        }

        if (empty($properties)) {
            throw new \Exception('updating property set is empty');
        }

        if (empty($this->where)) {
            if ($this->pkey_property && array_key_exists($this->pkey_property, $this->values)) {
                $this->where($this->pkey_property, $this->values[$this->pkey_property], '=');
            } else {
                throw new \Exception('no where condition set. will not update the whole table');
            }
        }

        $values = '';

        foreach ($properties as $p) {
            $values = $values . $this->fields[$p] . '=:' . $this->fields[$p] . ', ';
            $this->bind($p);
        }

        $values = substr($values, 0, -2);

        $sql = 'UPDATE ' . $this->table . ' '
            . 'SET ' . $values . ' '
            . 'WHERE ' . implode(' AND ', $this->where);
        $status = $this->db->query($sql, $this->bind_values);

        $this->clear();
        // undirty properties
        $this->properties_dirty = array_diff($this->properties_dirty, $properties);

        return $status;
    }

    public function getCount()
    {
        $this->setWhere(); // automatically add a filter for values we already have

        return intval(array_pop(array_pop($this->select('COUNT(*)'))));
    }

    /*
     * user input keys may have alias 'AS'
     *
     * return array with the primary key as index
     */

    public function getIndexedList($properties = '', $limit = false, $offset = false)
    {
        if (!$this->pkey_property) {
            throw new \Exception('Table does not have primary key. getIndexedList without primary key is not supported yet');
        }

        $list = [];

        foreach ($this->getList($keys, $limit, $offset) as $i) {
            $list[$i[$this->pkey_property]] = $i;
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
    public function getList($properties = '', $limit = false, $offset = false)
    {
        $this->setWhere(); // automatically add a filter for values we already have

        $fields = $this->selectFields($properties);

        return $this->select($fields, $limit, $offset);
    }

    /*
     * select query
     */

    private function selectFields($properties)
    {
        if (empty($properties)) {
            $properties_array = $this->properties;
            $fields = '';
            foreach ($properties_array as $p) {
                if ($p === $this->fields[$p]) {
                    $fields = $fields . $p . ', ';
                } else {
                    $fields = $fields . $this->fields[$p] . ' AS ' . $p . ', ';
                }
            }
        } else {
            $properties_tmp = explode(',', $properties);
            // add primary key property
            if ($this->pkey_property) {
                $properties_tmp[] = $this->pkey_property;
            }
            $properties_array = array_unique($properties_tmp);
            $fields = '';

            foreach ($properties_array as $p) {
                if (!in_array($p, $this->properties)) {
                    throw new \Exception('ERROR non-existing property : ' . $p);
                }
                if ($p === $this->fields[$p]) {
                    $fields = $fields . $p . ', ';
                } else {
                    $fields = $fields . $this->fields[$p] . ' AS ' . $p . ', ';
                }
            }
        }

        return substr($fields, 0, -2);
    }

    /**
     * Adds a condition for SQL query
     *
     * @param string $sql
     */
    public function where($prop, $value, $condition)
    {
        if (!in_array($prop, $this->properties)) {
            throw new \Exception('ERROR non-existing propperty : ' . $prop);
        }
        // NULL value
        if ($value === null) {
            $value = 'NULL';
            $condition = in_array($condition, ['=', 'is', 'IS']) ? 'IS' : 'IS NOT';
        } else {
            if (is_array($value)) {
                // a list of values
                if (sizeof($value) == 0) {
                    throw new \Exception('empty value set provided in where condition');
                }

                if (in_array(null, $value)) {
                    throw new \Exception('NULL provided in the value set. but NULL is not a value');
                }

                $value_clean = [];
                switch ($this->fields_type[$this->fields[$prop]]) {
                    case self::T_INT:
                        foreach ($value as $v) {
                            $value_clean[] = intval($v);
                        }
                        break;
                    case self::T_FLOAT:
                        foreach ($value as $v) {
                            $value_clean[] = floatval($v);
                        }
                        break;
                    case self::T_STRING:
                        foreach ($value as $v) {
                            $value_clean[] = $this->db->str($v);
                        }
                        break;
                    default:
                        throw new Exception('non-supported field data type: ' . $this->fields[$prop] . '(' . $this->fields_type[$this->fields[$prop]] . ')');
                }
                $value = '(' . implode(', ', $value_clean) . ')';
                $condition = in_array($condition, ['=', 'in', 'IN']) ? 'IN' : 'NOT IN';
            } else {
                $key = array_search($condition, ['>', '>=', '<', '<=', '=', '!=', '<>', 'LIKE', 'NOT LIKE'], true);
                if ($key === false) {
                    throw new Exception('non-supported operator: ' . $condition);
                }
                // a single value, bind
                $this->bind_values[':' . $prop . $key] = $value;
                $value = ':' . $prop . $key;
            }
        }

        $this->where[$prop . '_' . $condition] = $this->fields[$prop] . ' ' . $condition . ' ' . $value;
    }

    /**
     * Adds an SQL ORDER BY
     *
     * only order by current table's keys
     * user input keys will not have alias
     *
     * @param $key name of key with optional desc\asc seperated by one space
     */
    public function order($prop, $order = 'ASC')  //ASC or DESC
    {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new \Exception('wrong order : ' . $order);
        }

        if (in_array($prop, $this->properties)) {
            $this->order[$prop] = $this->fields[$prop] . ' ' . $order;
        } else {
            throw new \Exception('ERROR non-existing property : ' . $prop);
        }
    }

    private function setWhere()
    {
        // automatically add a filter for values we already have
        foreach (array_keys($this->values) as $prop) {
            $this->where($prop, $this->values[$prop], '=');
        }
    }

    private function select($properties = '', $limit = false, $offset = false)
    {
        $where = '';
        $order = '';

        if (sizeof($this->where) > 0) {
            $where = 'WHERE ' . implode(' AND ', $this->where);
        }

        if (sizeof($this->order) > 0) {
            $order = 'ORDER BY ' . implode(', ', $this->order);
        }

        $limit = ($limit > 0) ? 'LIMIT ' . $limit : '';
        $offset = ($offset > 0) ? 'OFFSET ' . $offset : '';

        $sql = 'SELECT ' . $properties
            . ' FROM ' . $this->table . ' '
            . $where . ' '
            . $order . ' '
            . $limit . ' '
            . $offset;

        $arr = $this->db->query($sql, $this->bind_values);
        $this->clear();

        return $arr;
    }

    private function getFieldsTypeAndPKey()
    {
        static $fields = [];
        static $int_type = ['int', 'bool'];
        static $float_type = ['float', 'double', 'real'];
        static $text_type = ['char', 'text', 'binary', 'blob', 'date', 'time', 'year'];

        if (array_key_exists($this->table, $fields)) {
            $this->fields_type = $fields[$this->table]['types'];
            $this->pkey_property = $fields[$this->table]['pkey'];
        } else {
            $res = $this->db->query('DESCRIBE ' . $this->table);

            foreach ($res as $r) {
                // primary key
                if ($r['Key'] == 'PRI') {
                    // no primary key found yet
                    if (is_null($this->pkey_property)) {
                        $prop = array_search($r['Field'], $this->fields);
                        if ($prop !== false) {
                            $this->pkey_property = $prop;
                        } else {
                            throw new \Exception('non-property primary key found : ' . $r['Field']);
                        }
                    } else {
                        throw new \Exception('found multiple primary keys in db table : ' . $this->fields[$this->pkey_property] . ', ' . $r['Field']);
                    }
                }

                // ignore fields that not set
                if (!in_array($r['Field'], $this->fields)) {
                    continue;
                }

                $found = false;

                foreach ($int_type as $i) {
                    if (strpos($r['Type'], $i) !== false) {
                        $this->fields_type[$r['Field']] = self::T_INT;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    foreach ($float_type as $f) {
                        if (strpos($r['Type'], $f) !== false) {
                            $this->fields_type[$r['Field']] = self::T_FLOAT;
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        foreach ($text_type as $t) {
                            if (strpos($r['Type'], $t) !== false) {
                                $this->fields_type[$r['Field']] = self::T_STRING;
                                $found = true;
                                break;
                            }
                        }
                    }
                }

                if (!$found) {
                    throw new \Exception('could not determine key type : ' . $r['Field'] . ' -> ' . $r['Type']);
                }
            }

            /*
            if (is_null($this->pkey_property))
            {
                throw new \Exception('no primary key found: ' . $this->table);
            }*/

            if (sizeof($this->fields_type) < sizeof($this->fields)) {
                throw new \Exception('the following fields do not exist in db table: ' . implode(', ', array_diff(array_values($this->fields), array_keys($this->fields_type))));
            }

            $fields[$this->table] = [
                'types' => $this->fields_type,
                'pkey' => $this->pkey_property
            ];
        }
    }
}
