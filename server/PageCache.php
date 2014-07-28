<?php

namespace site;

use site\Cache;
use site\CacheHandler;

/**
 * @property \site\SegmentCache[] $segments segments for page cache
 */
class PageCache extends Cache
{

   static protected $_format = '.html.gz';
   protected $_segments = [ ];

   /**
    * 
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
      if ( $this->_dirty )
      {
         $this->_id = self::$_handler->getID( $this->_key );

         // unlink existing parent cache nodes
         self::$_handler->unlinkParents( $this->_id );

         // update self
         if ( $this->_deleted )
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
               self::$_handler->linkParents( $this->_id, $this->_parents );
            }
         }

         // flush/delete child cache nodes
         $this->_deleteChildren();

         $this->_dirty = FALSE;
      }
   }

}

//__END_OF_FILE__
