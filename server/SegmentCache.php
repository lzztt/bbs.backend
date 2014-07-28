<?php

namespace site;

use site\Cache;
use site\CacheHandler;

class SegmentCache extends Cache
{

   static protected $_format = '.txt';

   /**
    * fetch segment data from cache
    * @return boolean
    */
   public function fetch()
   {
      if ( $this->_data )
      {
         return $this->_data;
      }

      return $this->_fetchFromFile();
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
               // save self
               $this->_writeDataFile( $this->_data );

               // link to current parent nodes
               self::$_handler->linkParents( $this->_id, $this->_parents );
            }
         }

         // delete(flush) child cache nodes
         $this->_deleteChildren();

         $this->_dirty = FALSE;
      }
   }

   public function _fetchFromFile()
   {
      $file = $this->_getFileName();
      try
      {
         // read only if exist!!
         return \is_file( $file ) ? \file_get_contents( $file ) : NULL;
      }
      catch ( \Exception $e )
      {
         $this->logger->warn( 'Could not read from file [' . $file . ']: ' . $e->getMessage() );
         return NULL;
      }
   }

}

//__END_OF_FILE__
