<?php

namespace site;

/**
 * Description of PageCache
 *
 * @author ikki
 */
class PageCache
{

   static public $path = '/tmp/www.houstonbbs.com/public';
   protected $uri;
   protected $segments;
   protected $data;

   public function __construct( $uri )
   {
      $this->uri = $uri;
      $this->segments = [ ];
   }

   public function store( $data )
   {
      $this->data = $data;
   }

   /**
    * 
    * @param type $key
    * @return SegmentCache
    */
   public function fetchSegment( $key )
   {
      if ( !\array_key_exists( $key, $this->segments ) )
      {
         $this->segments[ $key ] = new SegmentCache( $key );
      }
      return $this->segments[ $key ];
   }

   public function flush()
   {
      // fluch page cache
      $this->_saveToFile();

      // flush all segments
      foreach ( $this->segments as $seg )
      {
         $seg->flush();
         
         // save cache map
      }
      
      
   }
   
   protected function _saveMap()
   {
      
   }

   protected function _saveToFile()
   {
      \file_put_contents( $this->_getFileName(), $this->data, \LOCK_EX );
   }

   protected function _getFileName()
   {
      static $_filename;

      if ( !$_filename )
      {
         $_filename = self::$path . ( \strpos( $uri, '?' ) ? \str_replace( '?', '#', $uri ) : ($uri . '#') ) . '.html.gz';
      }

      return $_filename;
   }

}

//__END_OF_FILE__
