<?php declare(strict_types=1);

namespace lzx\db;

use Exception;
use ReflectionObject;
use ReflectionProperty;

/*
 * support tables with one primary key
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
    private $exists = null;
    private $where = [];
    private $bind_values = [];
    private $order = [];

    public function __construct(DB $db, string $table, int $id = 0, string $properties = '')
    {
        $this->properties = array_map(function (ReflectionProperty $prop) {
            return $prop->getName();
        }, (new ReflectionObject($this))->getProperties(ReflectionProperty::IS_PUBLIC));

        $this->db = $db;
        $this->table = $table;
        $this->fields = [];
        foreach ($this->properties as $p) {
            $this->fields[$p] = self::camelToUnderscore($p);
        }

        $this->getFieldsTypeAndPKey();

        if ($id > 0) {
            if (!$this->pkey_property) {
                throw new Exception('Table does not have primary key. Cannot assign id=' . $id);
            }
            $this->setValue($this->pkey_property, $id);
            $props = $this->parseProperties($properties, $this->properties);
            if (!in_array($this->pkey_property, $props) || sizeof($props) > 1) {
                $this->load($properties);
            }
        }
    }

    private static function camelToUnderscore(string $name): string
    {
        static $cache = [];
        if (!array_key_exists($name, $cache)) {
            $cache[$name] = strtolower(preg_replace('/([A-Z])/', '_$0', $name));
        }
        return $cache[$name];
    }

    private static function underscoreToCamel(string $name): string
    {
        static $cache = [];
        if (!array_key_exists($name, $cache)) {
            $cache[$name] = str_replace('_', '', lcfirst(ucwords($name, '_')));
        }
        return $cache[$name];
    }

    public function __set(string $prop, $val)
    {
        throw new Exception('unknown property: ' . $prop);
    }

    public function __get(string $prop)
    {
        throw new Exception('unknown property: ' . $prop);
    }

    public function __isset(string $prop)
    {
        throw new Exception('unknown property: ' . $prop);
    }

    public function __unset(string $prop)
    {
        throw new Exception('unknown property: ' . $prop);
    }

    public function toArray(): array
    {
        $this->sync();
        return $this->values;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    private function bind(string $prop): void
    {
        if (array_key_exists($prop, $this->values)) {
            $this->bind_values[':' . $this->fields[$prop]] = $this->values[$prop];
        } else {
            throw new Exception('could not find binding value for property: ' . $prop);
        }
    }

    private function clear(bool $clearData = false): void
    {
        $this->where = [];
        $this->bind_values = [];
        $this->order = [];
        if ($clearData) {
            $this->values = [];
            $this->properties_dirty = [];
            $this->exists = null;
        }
    }

    private function setValue(string $prop, $value): void
    {
        if (is_null($value)) {
            $this->values[$prop] = null;
        } else {
            if (!in_array(gettype($value), ['integer', 'string', 'double', 'boolean'])) {
                throw new Exception('Invalid value type: ' . $this->fields[$prop] . '(' . gettype($value) . ')');
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
                    throw new Exception('non-supported field data type: ' . $this->fields[$prop] . '(' . $this->fields_type[$this->fields[$prop]] . ')');
            }
        }

        $this->$prop = $this->values[$prop];
    }

    private function sync(): void
    {
        foreach ($this->properties as $p) {
            if ($this->$p === $this->values[$p]) {
                continue;
            }
            $this->setValue($p, $this->$p);
            // mark property as dirty
            if (!in_array($p, $this->properties_dirty)) {
                $this->properties_dirty[] = $p;
            }
        }
    }

    private function clean(string $prop): void
    {
        $dirty = array_search($prop, $this->properties_dirty);
        if ($dirty !== false) {
            unset($this->properties_dirty[$dirty]);
        }
    }

    /**
     * Call a database procedure
     */
    public function call(string $proc, array $params = []): array
    {
        return $this->db->query('CALL ' . $proc, $params);
    }

    // convert array keys from column names to field names
    protected function convertColumnNames(array $arr): array
    {
        foreach ($arr as $i => $row) {
            foreach ($row as $col => $val) {
                $newCol = self::underscoreToCamel($col);
                if ($newCol !== $col) {
                    $arr[$i][$newCol] = $val;
                    unset($arr[$i][$col]);
                }
            }
        }
        return $arr;
    }

    /**
     * Loads values to instance from DB
     */
    public function load(string $properties = ''): void
    {
        $this->clear();

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
        }
    }

    public function exists(): bool
    {
        if (is_null($this->exists)) {
            if (!isset($this->{$this->pkey_property})) {
                return false;
            }
            $this->load($this->pkey_property);
        }
        return $this->exists;
    }

    public function delete(): void
    {
        if (!$this->pkey_property) {
            throw new Exception('Table does not have primary key. Deletion without primary key is not supported yet');
        }

        $this->sync();

        if (array_key_exists($this->pkey_property, $this->values)) {
            $this->bind($this->pkey_property);
            $this->db->query('DELETE FROM ' . $this->table . ' WHERE ' . $this->fields[$this->pkey_property] . ' = :' . $this->fields[$this->pkey_property], $this->bind_values);
            $this->clear(true);
        } else {
            throw new Exception('ERROR delete: invalid primary key value: [' . $this->fields[$this->pkey_property] . ' : ' . $this->values[$this->pkey_property] . ']');
        }
    }

    /**
     * Insert a record
     */
    public function add(): void
    {
        $this->sync();

        if (sizeof($this->properties_dirty) == 0) {
            throw new Exception('adding an object with no dirty properties to database');
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
            $this->setValue($this->pkey_property, $this->db->insertId());
        }

        $this->clear();
        // undirty all properties
        $this->properties_dirty = [];
        $this->exists = true;
    }

    /**
     * Update a record / records
     */
    public function update(string $properties = ''): void
    {
        $this->sync();

        // do not update pkey
        if ($this->pkey_property) {
            $this->clean($this->pkey_property);
        }

        if (sizeof($this->properties_dirty) == 0) {
            return;
        }

        $props = $this->parseProperties($properties, $this->properties_dirty);

        if (empty($props)) {
            throw new Exception('updating property set is empty');
        }

        if (empty($this->where)) {
            if ($this->pkey_property && array_key_exists($this->pkey_property, $this->values)) {
                $this->where($this->pkey_property, $this->values[$this->pkey_property], '=');
            } else {
                throw new Exception('no where condition set. will not update the whole table');
            }
        }

        $values = '';

        foreach ($props as $p) {
            $values = $values . $this->fields[$p] . '=:' . $this->fields[$p] . ', ';
            $this->bind($p);
        }

        $values = substr($values, 0, -2);

        $sql = 'UPDATE ' . $this->table . ' '
            . 'SET ' . $values . ' '
            . 'WHERE ' . implode(' AND ', $this->where);
        $this->db->query($sql, $this->bind_values);

        $this->clear();
        // undirty properties
        $this->properties_dirty = array_diff($this->properties_dirty, $props);
    }

    public function getCount(): int
    {
        $this->sync();
        $this->setWhere(); // automatically add a filter for values we already have

        return intval(array_pop(array_pop($this->select('COUNT(*)'))));
    }

    /**
     * Selects from DB, returns array
     *
     * user input keys may have alias
     * will always get primary key values
     */
    public function getList(string $properties = '', int $limit = 0, int $offset = 0): array
    {
        $this->sync();
        $this->setWhere(); // automatically add a filter for values we already have

        $props = $this->parseProperties($properties, $this->properties);
        if (!in_array($this->pkey_property, $props)) {
            $props[] = $this->pkey_property;
        }
        $fields = $this->selectFields($props);

        return $this->select($fields, $limit, $offset);
    }

    private function parseProperties(string $properties, array $property_pool): array
    {
        if (!$properties) {
            return $property_pool;
        }

        $props = array_unique(explode(',', $properties));
        $props_exist = array_intersect($props, $property_pool);
        if (sizeof($props_exist) != sizeof($props)) {
            throw new Exception('ERROR disallowed properties: ' . implode(',', array_diff($props, $props_exist)));
        }
        return $props_exist;
    }

    private function selectFields(array $properties): string
    {
        $fields = '';
        foreach ($properties as $p) {
            if ($p === $this->fields[$p]) {
                $fields = $fields . $p . ', ';
            } else {
                $fields = $fields . $this->fields[$p] . ' AS ' . $p . ', ';
            }
        }

        return substr($fields, 0, -2);
    }

    /**
     * Adds a condition for SQL query
     */
    public function where(string $prop, $value, string $condition): void
    {
        if (!in_array($prop, $this->properties)) {
            throw new Exception('ERROR non-existing propperty : ' . $prop);
        }
        // NULL value
        if ($value === null) {
            $value = 'NULL';
            $condition = in_array($condition, ['=', 'is', 'IS']) ? 'IS' : 'IS NOT';
        } else {
            if (is_array($value)) {
                // a list of values
                if (sizeof($value) == 0) {
                    throw new Exception('empty value set provided in where condition');
                }

                if (in_array(null, $value)) {
                    throw new Exception('NULL provided in the value set. but NULL is not a value');
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
     */
    public function order(string $prop, string $order = 'ASC'): void
    {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new Exception('wrong order : ' . $order);
        }

        if (in_array($prop, $this->properties)) {
            $this->order[$prop] = $this->fields[$prop] . ' ' . $order;
        } else {
            throw new Exception('ERROR non-existing property : ' . $prop);
        }
    }

    private function setWhere(): void
    {
        // automatically add a filter for values we already have
        foreach (array_keys($this->values) as $prop) {
            $this->where($prop, $this->values[$prop], '=');
        }
    }

    private function select(string $properties = '', int $limit = 0, int $offset = 0): array
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

    private function getFieldsTypeAndPKey(): void
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
                            throw new Exception('non-property primary key found : ' . $r['Field']);
                        }
                    } else {
                        throw new Exception('found multiple primary keys in db table : ' . $this->fields[$this->pkey_property] . ', ' . $r['Field']);
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
                    throw new Exception('could not determine key type : ' . $r['Field'] . ' -> ' . $r['Type']);
                }
            }

            if (sizeof($this->fields_type) < sizeof($this->fields)) {
                throw new Exception('the following fields do not exist in db table: ' . implode(', ', array_diff(array_values($this->fields), array_keys($this->fields_type))));
            }

            $fields[$this->table] = [
                'types' => $this->fields_type,
                'pkey' => $this->pkey_property
            ];
        }
    }
}
