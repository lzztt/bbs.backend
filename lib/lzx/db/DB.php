<?php

namespace lzx\db;

/**
 * @param \PDO $db
 */
class DB
{

   protected static $instances = [];
   public $queries = [];
   public $debugMode = FALSE;
   protected $db;
   protected $statements = [];

   /*
    * @return lzx\db\DB $instance
    */

   // Singleton methord for each database
   public static function getInstance( array $config = [] )
   {
      // no config
      if ( \count( $config ) == 0 && \count( self::$instances ) > 0 )
      {
         return \end( self::$instances );
      }

      // only has dsn
      if ( \count( $config ) == 1 && \array_key_exists( 'dsn', $config ) && \array_key_exists( $config['dsn'], self::$instances ) )
      {
         return self::$instances[$config['dsn']];
      }

      foreach ( ['dsn', 'user', 'password'] as $key )
      {
         if ( !\array_key_exists( $key, $config ) )
         {
            throw new \InvalidArgumentException( 'missing database parameters : ' . $key );
         }
      }

      $instance = new self( $config );
      // save
      self::$instances[$config['dsn']] = $instance;

      return $instance;
   }

   private function __construct( array $config = [] )
   {
      $this->db = new \PDO( $config['dsn'], $config['user'], $config['password'], array(\PDO::ATTR_PERSISTENT => TRUE) );
      $this->db->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
      $this->db->beginTransaction();
   }

   public function __destruct()
   {
      try
      {
         $this->db->commit();
      }
      catch ( \PDOException $e )
      {
         $this->db->rollBack();
         throw $e;
      }
   }

   /**
    * Returns result resource from given query
    *
    * @param string $sql
    * @return MySQLi_Result
    */
   public function query( $sql, array $params = [] )
   {
      if ( empty( $params ) )
      {
         if ( !$this->debugMode )
         {
            $statement = $this->db->query( $sql, \PDO::FETCH_ASSOC );
         }
         else
         {
            // query debug timer and info
            $_timer = \microtime( TRUE );
            $statement = $this->db->query( $sql, \PDO::FETCH_ASSOC );
            $this->queries[] = \sprintf( '%8.6f', \microtime( TRUE ) - $_timer ) . ' : [QUERY] ' . $sql;
         }
      }
      else
      {
         if ( !$this->debugMode )
         {
            if ( !\in_array( $sql, $this->statements ) )
            {
               $this->statements[$sql] = $this->db->prepare( $sql );
            }
            $statement = $this->statements[$sql];
            foreach ( $params as $k => $v )
            {
               $statement->bindValue( $k, $v );
            }
            $statement->execute();
         }
         else
         {
            // query debug timer and info
            if ( !\in_array( $sql, $this->statements ) )
            {
               $_timer = \microtime( TRUE );
               $this->statements[$sql] = $this->db->prepare( $sql );
               $this->queries[] = \sprintf( '%8.6f', \microtime( TRUE ) - $_timer ) . ' : [PREPARE] ' . $sql;
            }
            $statement = $this->statements[$sql];
            $_timer = \microtime( TRUE );
            foreach ( $params as $k => $v )
            {
               $statement->bindValue( $k, $v );
            }
            $statement->execute();
            $sql .= ' [ ';
            foreach ( $params as $k => $v )
            {
               $sql = $sql . $k . '=' . $v . ', ';
            }
            $sql = \substr( $sql, 0, -2 ) . ' ]';
            $this->queries[] = \sprintf( '%8.6f', \microtime( TRUE ) - $_timer ) . ' : [EXECUTE] ' . $sql;
         }
      }

      $res = $statement->columnCount() > 0 ? $statement->fetchAll( \PDO::FETCH_ASSOC ) : TRUE;
      $statement->closeCursor();

      return $res;
   }

   public function insert_id()
   {
      return $this->db->lastInsertId();
   }

   public function str( $str )
   {
      if ( !$this->debugMode )
      {
         return $this->db->quote( $str );
      }
      else
      {
// query debug timer and info
         $_timer = \microtime( TRUE );
         $_str = $this->db->quote( $str );
         $this->queries[] = \sprintf( '%8.6f', \microtime( TRUE ) - $_timer ) . ' : [STRING] ' . $str;
         return $_str;
      }
   }

}

//__END_OF_FILE__