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
   static protected $_format = '.html.gz';
   protected $_segments = [ ];

   /**
    * 
    * @param type $key
    * @return SegmentCache
    */
   public function getSegment( $key )
   {
      $cleanKey = $this->_getCleanKey( $key );

      if ( !\array_key_exists( $cleanKey, $this->_segments ) )
      {
         $this->_segments[ $cleanKey ] = new SegmentCache( $key );
      }

      return $this->_segments[ $cleanKey ];
   }

   public function flush()
   {
      if ( self::$status )
      {
         $this->_initDB();
         $this->_id = $this->_getID();

         // unlink exiting parent cache nodes
         $this->_unlinkParents();

         // update self
         if ( $this->_isDeleted )
         {
            // delete self
            $this->_deleteDataFile();
         }
         else
         {
            if ( $this->_data )
            {
               // save (flush) all segments first, this may delete segment's children (this cache)
               foreach ( $this->_segments as $seg )
               {
                  $seg->flush();
               }

               // save self
               // gzip data for public cache file used by webserver
               // use 6 as default and equal to webserver gzip compression level
               $this->_writeDataFile( \gzencode( $this->_data, 6 ) );

               // make segments as parent nodes
               foreach ( \array_keys( $this->_segments ) as $pkey )
               {
                  $this->_parents[] = $pkey;
               }

               // link to current parent nodes
               $this->_linkParents();
            }
         }

         // flush/delete child cache nodes
         $this->_deleteChildren();
      }
   }

}

//__END_OF_FILE__
