<?php

namespace lzx\core;

use lzx\core\Logger;

class MySQL extends \mysqli
{

   public static $queries = array();
   public static $hasError = FALSE;
   public $debugMode = FALSE;
   private $logger;
   private $db;
   private $result;
   private $r;

   private function __construct($host, $username, $passws, $dbname)
   {
      $this->db = $dbname;

      parent::__construct($host, $username, $passws, $dbname);

      if ($this->connect_error)
      {
         $this->logError('Could not connect to database: ' . $this->connect_error, NULL);
         return;
      }

      if ($this->set_charset('utf8') === FALSE)
      {
         $this->logError('Could not set default character set: ' . $this->error);
      }
   }

   // Singleton methord for each database
   /**
    * @return MySQL
    * @return mysqli
    */
   public static function getInstance($config = NULL, $setAsDefault = FALSE)
   {
      static $instances = array();

      $default_key = 'default';

      if (empty($config) && \array_key_exists($default_key, $instances))
      {
         return $instances[$default_key];
      }

      $required_config_keys = array('host', 'username', 'passwd', 'dbname');
      if(!\is_object($config))
      {
         $config = (object) $config;
      }

      foreach ($required_config_keys as $key)
      {
         if (empty($config->$key))
         {
            throw new \Exception('missing database parameters : ' . $key);
         }
      }

      $key = \serialize($config);

      if (!\array_key_exists($key, $instances))
      {
         $instances[$key] = new self($config->host, $config->username, $config->passwd, $config->dbname);
         if ($setAsDefault)
         {
            $instances[$default_key] = $instances[$key];
         }
      }

      return $instances[$key];
   }

   public function setLogger(Logger $logger)
   {
      $this->logger = $logger;
   }

   private function logError($err, $sql = null)
   {
      self::$hasError = TRUE;
      $log = "Database Error\n"
         . (isset($this->db) ? '[DB] ' . $this->db . \PHP_EOL : '')
         . (isset($sql) ? '[SQL] ' . $sql . \PHP_EOL : '')
         . (empty($err) ? '' : '[DBMSG] ' . $err);

      if ($this->logger instanceof Logger)
      {
         $this->logger->error(\trim($log), 4);
      }
      else
      {
         throw new \Exception('no logger found for MySQL object');
      }
   }

   /**
    * Returns result resource from given query
    *
    * @param string $sql
    * @return MySQLi_Result
    */
   public function query($sql)
   {
      if (self::$hasError)
      {
         return FALSE;
      }

      if ($this->debugMode)
      {
         // query debug timer and info
         $_timer = \microtime(TRUE);
         $this->result = parent::query($sql);
         self::$queries[] = \sprintf('%8.6f', \microtime(TRUE) - $_timer) . ' : ' . $sql;
      }
      else
      {
         $this->result = parent::query($sql);
      }

      if ($this->result === FALSE)
      {
         $this->logError($this->error, $sql);
      }

      return $this->result;
   }

   /**
    * Loads next row into $this->r
    * for use in while loops
    *
    * @return bool
    */
   public function next()
   {
      if ($this->result instanceof \mysqli_result)
      {
         $this->r = $this->result->fetch_assoc();
         return isset($this->r);
      }
      else
      {
         return FALSE;
      }
   }

   public function insert_id()
   {
      return $this->insert_id;
   }

   public function affected_rows()
   {
      return $this->affected_rows;
   }

   public function num_rows()
   {
      if ($this->result instanceof \mysqli_result)
      {
         return $this->result->num_rows;
      }
   }

   public function num_fields()
   {
      if ($this->result instanceof \mysqli_result)
      {
         return $this->result->field_count;
      }
   }

   public function field_type()
   {
      $type = array();
      if ($this->result instanceof \mysqli_result)
      {
         foreach ($this->result->fetch_fields() as $f)
         {
            $type[$f->name] = $f->type;
         }
      }
      return $type;
   }

   public function escape($str)
   {
      if (\is_array($str))
      {
         $arr = array();
         foreach ($str as $k => $v)
         {
            $arr[$k] = $this->real_escape_string($v);
         }
         return $arr;
      }
      else
      {
         return $this->real_escape_string($str);
      }
   }

   public function sanitize($str)
   {
      return $this->escape($str);
   }

   /**
    * Returns full result (assoc array) from given query
    *
    * @param string $sql
    * @return array()
    */
   public function select($sql)
   {
      $this->query($sql);

      $arr = array();
      while ($this->next())
      {
         $arr[] = $this->r;
      }

      return $arr;
   }

   /**
    * Returns a single row from given query
    *
    * @param string $sql
    * @return array()
    */
   public function row($sql)
   {
      $this->query($sql);

      $row = array();
      if ($this->next())
      {
         $row = $this->r;
      }

      return $row;
   }

   /**
    * Returns a single value from given query
    *
    * @param string $sql
    * @return string result value or NULL
    */
   public function val($sql)
   {
      $this->query($sql);

      $val = null;
      if ($this->next())
      {
         $key = array_keys($this->r);
         $val = $this->r[$key[0]];
      }

      return $val;
   }

   public function str($str)
   {
      return '"' . $this->real_escape_string($str) . '"';
   }

   public function free()
   {
      if ($this->result instanceof \mysqli_result)
      {
         $this->result->free();
      }
   }

}

//__END_OF_FILE__