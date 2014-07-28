<?php

namespace site;

use lzx\db\DB;

/**
 * Description of CacheName
 *
 * @author ikki
 */
class CacheHandler
{

   protected $_db;
   protected $_ids = [ ];

   private function __construct()
   {
      $this->_db = DB::getInstance();
   }

   /**
    * singleton design pattern
    * 
    * @staticvar self $instance
    * @return \site\CacheHandler
    */
   public static function getInstance()
   {
      static $instance;

      if ( !isset( $instance ) )
      {
         $instance = new self();
      }
      return $instance;
   }

   public function getID( $name )
   {
      // found from cached id
      if ( \array_key_exists( $name, $this->_ids ) )
      {
         return $this->_ids[ $name ];
      }

      // found from database
      $res = $this->_db->query( 'SELECT id FROM cache WHERE name = :key', [ ':key' => $name ] );
      switch ( \count( $res ) )
      {
         case 0:
            // add to database
            $this->_db->query( 'INSERT INTO cache (name) VALUEs (:key)', [ ':key' => $name ] );
            // save to id cache
            $this->_ids[ $name ] = (int) $this->_db->insert_id();
            break;
         case 1:
            // save to id cache
            $this->_ids[ $name ] = (int) \array_pop( $res[ 0 ] );
            break;
         default :
            throw new \Exception( 'multiple ID found for name: ' . $name );
      }

      return $this->_ids[ $name ];
   }

   public function unlinkParents( $id )
   {
      $this->_db->query( 'DELETE FROM cache_tree WHERE cid = :cid', [ ':cid' => $id ] );
   }

   public function linkParents( $id, Array $parents )
   {
      if ( $parents )
      {
         \array_unique( $parents );

         $values = [ ];
         foreach ( $parents as $key )
         {
            $values[] = '(' . $this->getID( $key ) . ',' . $id . ')';
         }

         $this->_db->query( 'INSERT INTO cache_tree VALUES ' . \implode( ',', $values ) );
      }
   }

   public function linkChildren( $id, Array $children )
   {
      if ( $children )
      {
         \array_unique( $children );

         $values = [ ];
         foreach ( $children as $key )
         {
            $values[] = '(' . $id . ',' . $this->getID( $key ) . ')';
         }

         $this->_db->query( 'INSERT INTO cache_tree VALUES ' . \implode( ',', $values ) );
      }
   }

   public function getChildren( $id )
   {
      $children = $this->_db->query( 'SELECT id, name FROM cache WHERE id IN (SELECT cid FROM cache_tree WHERE pid = :pid)', [ ':pid' => $id ] );
      foreach ( $children as $c )
      {
         $this->_ids[ $c[ 'name' ] ] = $c[ 'id' ];
      }

      return \array_column( $children, 'name' );
   }

}

//__END_OF_FILE__
