<?php

namespace site;

/**
 * Description of SegmentCache
 *
 * @author ikki
 */
class SegmentCache
{

   static public $path = '/tmp/www.houstonbbs.com/private';
   protected $key;
   protected $data;

   public function __construct( $key )
   {
      $this->key = $key;
   }

   public function fetch()
   {
      if( $this->data )
      {
         return $this->data;
      }
      
      $file = $this->_getFileName();
      try
      {
         // read only if exist!!
         return \is_file( $file ) ? \file_get_contents( $file ) : FALSE;
      }
      catch ( \Exception $e )
      {
         $this->logger->warn( 'Could not read from file [' . $file . ']: ' . $e->getMessage() );
         return FALSE;
      }
   }

   public function store( $data )
   {
      $this->data = (string) $data;
   }
   
   public function addMap( $cachekey )
   {
      
   }

   public function flush()
   {
      $this->_saveToFile();
   }

   protected function _saveToFile()
   {
      \file_put_contents( $this->_getFileName(), $this->data, \LOCK_EX );
   }

   protected function _getFileName()
   {
      static $_filename;
      
      if( !$_filename )
      {
         $_filename = self::$path . '/' . \preg_replace( '/[^0-9a-z\.\_\-]/i', '_', $_key ) . '.txt';
      }
      
      return $_filename;
   }

}

//__END_OF_FILE__
