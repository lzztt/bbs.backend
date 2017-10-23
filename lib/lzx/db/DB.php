<?php

namespace lzx\db;

/**
 * @param \PDO $_db
 */
class DB
{
    public static $debug = false;
    protected static $_instances = [];
    public $queries = [];
    protected $_db;
    protected $_statements = [];

    /*
     * @return lzx\db\DB
     */

    // Singleton methord for each database
    public static function getInstance(array $config = [])
    {
        // no config
        if (\count($config) == 0 && \count(self::$_instances) > 0) {
            return \end(self::$_instances);
        }

        // only has dsn
        if (\count($config) == 1 && \array_key_exists('dsn', $config) && \array_key_exists($config['dsn'], self::$_instances)) {
            return self::$_instances[$config['dsn']];
        }

        foreach (['dsn', 'user', 'password'] as $key) {
            if (!\array_key_exists($key, $config)) {
                throw new \InvalidArgumentException('missing database parameters : ' . $key);
            }
        }

        $instance = new self($config);
        // save
        self::$_instances[$config['dsn']] = $instance;

        return $instance;
    }

    private function __construct(array $config = [])
    {
        $this->_db = new \PDO($config['dsn'], $config['user'], $config['password'], [
            \PDO::ATTR_PERSISTENT => true,
            \PDO::ATTR_AUTOCOMMIT => false,
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            ]);
        // this is NEEDED, even AUTOCOMMIT = FALSE :(
        $this->_db->beginTransaction();
    }

    public function __destruct()
    {
        try {
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }
    }

    public function flush()
    {
        try {
            $this->_db->commit();
        } catch (\PDOException $e) {
            $this->_db->rollBack();
            throw $e;
        }
        $this->queries = [];
        $this->_db->beginTransaction();
    }

    /**
     * Returns result resource from given query
     *
     * @param string $sql
     * @return MySQLi_Result
     */
    public function query($sql, array $params = [])
    {
        if (empty($params)) {
            if (!self::$debug) {
                $statement = $this->_db->query($sql, \PDO::FETCH_ASSOC);
            } else {
                // query debug timer and info
                $_timer = \microtime(true);
                $statement = $this->_db->query($sql, \PDO::FETCH_ASSOC);
                $this->queries[] = \sprintf('%8.6f', \microtime(true) - $_timer) . ' : [QUERY] ' . $sql;
            }
        } else {
            if (!self::$debug) {
                if (!\array_key_exists($sql, $this->_statements)) {
                    $this->_statements[$sql] = $this->_db->prepare($sql);
                }
                $statement = $this->_statements[$sql];
                foreach ($params as $k => $v) {
                    $statement->bindValue($k, $v);
                }
                $statement->execute();
            } else {
                // query debug timer and info
                if (!\array_key_exists($sql, $this->_statements)) {
                    $_timer = \microtime(true);
                    $this->_statements[$sql] = $this->_db->prepare($sql);
                    $this->queries[] = \sprintf('%8.6f', \microtime(true) - $_timer) . ' : [PREPARE] ' . $sql;
                }
                $statement = $this->_statements[$sql];
                $_timer = \microtime(true);
                foreach ($params as $k => $v) {
                    $statement->bindValue($k, $v);
                }
                $statement->execute();
                $sql .= ' [';
                foreach ($params as $k => $v) {
                    $sql = $sql . $k . '=' . $v . ', ';
                }
                $sql = \substr($sql, 0, -2) . ']';
                $this->queries[] = \sprintf('%8.6f', \microtime(true) - $_timer) . ' : [EXECUTE] ' . $sql;
            }
        }

        $res = $statement->columnCount() > 0 ? $statement->fetchAll(\PDO::FETCH_ASSOC) : true;
        $statement->closeCursor();

        return $res;
    }

    public function insert_id()
    {
        return $this->_db->lastInsertId();
    }

    public function str($str)
    {
        if (!self::$debug) {
            return $this->_db->quote($str);
        } else {
            // query debug timer and info
            $_timer = \microtime(true);
            $_str = $this->_db->quote($str);
            $this->queries[] = \sprintf('%8.6f', \microtime(true) - $_timer) . ' : [STRING] ' . $str;
            return $_str;
        }
    }
}

//__END_OF_FILE__
