<?php declare(strict_types=1);

namespace lzx\db;

use InvalidArgumentException;
use PDO;
use PDOException;

class DB
{
    public static $debug = false;
    protected static $instances = [];
    public $queries = [];
    protected $db;
    protected $statements = [];

    private function __construct(array $config = [])
    {
        $this->db = new PDO($config['dsn'], $config['user'], $config['password'], [
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_AUTOCOMMIT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => false,
        ]);
        $this->db->beginTransaction();
    }

    public function __destruct()
    {
        try {
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public static function getInstance(array $config = []): DB
    {
        // no config
        if (count($config) == 0 && count(self::$instances) > 0) {
            return end(self::$instances);
        }

        // only has dsn
        if (count($config) == 1 && array_key_exists('dsn', $config) && array_key_exists($config['dsn'], self::$instances)) {
            return self::$instances[$config['dsn']];
        }

        foreach (['dsn', 'user', 'password'] as $key) {
            if (!array_key_exists($key, $config)) {
                throw new InvalidArgumentException('missing database parameters : ' . $key);
            }
        }

        $instance = new self($config);
        // save
        self::$instances[$config['dsn']] = $instance;

        return $instance;
    }

    public function flush(): void
    {
        try {
            $this->db->commit();
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
        $this->queries = [];
        $this->db->beginTransaction();
    }

    /**
     * Returns result resource from given query
     */
    public function query(string $sql, array $params = []): array
    {
        if (empty($params)) {
            if (!self::$debug) {
                $statement = $this->db->query($sql, PDO::FETCH_ASSOC);
            } else {
                // query debug timer and info
                $timer = microtime(true);
                $statement = $this->db->query($sql, PDO::FETCH_ASSOC);
                $this->queries[] = sprintf('%8.6f', microtime(true) - $timer) . ' : [QUERY] ' . $sql;
            }
        } else {
            if (!self::$debug) {
                if (!array_key_exists($sql, $this->statements)) {
                    $this->statements[$sql] = $this->db->prepare($sql);
                }
                $statement = $this->statements[$sql];
                $statement->execute($params);
            } else {
                // query debug timer and info
                if (!array_key_exists($sql, $this->statements)) {
                    $timer = microtime(true);
                    $this->statements[$sql] = $this->db->prepare($sql);
                    $this->queries[] = sprintf('%8.6f', microtime(true) - $timer) . ' : [PREPARE] ' . $sql;
                }
                $statement = $this->statements[$sql];
                $timer = microtime(true);
                $statement->execute($params);
                $sql .= ' [';
                foreach ($params as $k => $v) {
                    $sql = $sql . $k . '=' . $v . ', ';
                }
                $sql = substr($sql, 0, -2) . ']';
                $this->queries[] = sprintf('%8.6f', microtime(true) - $timer) . ' : [EXECUTE] ' . $sql;
            }
        }

        $res = $statement->columnCount() > 0 ? $statement->fetchAll(PDO::FETCH_ASSOC) : [];
        $statement->closeCursor();

        return $res;
    }

    public function insertId(): string
    {
        return $this->db->lastInsertId();
    }

    public function str(string $str): string
    {
        if (!self::$debug) {
            return $this->db->quote($str);
        } else {
            // query debug timer and info
            $timer = microtime(true);
            $str = $this->db->quote($str);
            $this->queries[] = sprintf('%8.6f', microtime(true) - $timer) . ' : [STRING] ' . $str;
            return $str;
        }
    }
}
