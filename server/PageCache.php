<?php

namespace site;

use site\Cache;

/**
 * @property string $uri URI key for page cache
 * @property \site\SegmentCache "[]" $segments segments for page cache
 * @property string $data caching data
 * @property \lzx\db\DB $_db cache tree database
 */
class PageCache extends Cache
{

   static public $path = '/tmp/www.houstonbbs.com/public';
   static protected $format = '.html.gz';
   protected $segments = [ ];

   /**
    * 
    * @param type $key
    * @return SegmentCache
    */
   public function getSegment( $key )
   {
      $cleanKey = $this->_getCleanKey( $key );

      if ( !\array_key_exists( $cleanKey, $this->segments ) )
      {
         $this->segments[ $cleanKey ] = new SegmentCache( $key );
      }

      return $this->segments[ $cleanKey ];
   }

   public function flush()
   {
      if ( self::$status )
      {
         // unlink exiting parent cache nodes
         $this->_unlinkParents( $this->key );

         // update self
         if ( $this->isDeleted )
         {
            // delete self
            $this->_deleteDataFile();
         }
         else
         {
            if ( $this->data )
            {
               // save (flush) all segments first
               foreach ( $this->segments as $seg )
               {
                  $seg->flush();
               }

               // save self
               // gzip data for public cache file used by webserver
               // use 6 as default and equal to webserver gzip compression level
               $this->_writeDataFile( \gzencode( $this->data, 6 ) );

               // link to current parent nodes
               foreach ( $this->parents as $p )
               {
                  $this->_saveMap( $p, $this->key );
               }

               // link to its segments as parent nodes
               foreach ( \array_keys( $this->segments ) as $p )
               {
                  $this->_saveMap( $p, $this->key );
               }
            }
         }

         // flush/delete child cache nodes
         $this->_deleteChildren();
      }
   }

}

//__END_OF_FILE__
