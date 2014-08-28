<?php

namespace lzx\cache;

use lzx\cache\Cache;

class SegmentCache extends Cache
{

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
         self::$_handler->unlinkEvents( $this->_id );

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
      $file = self::$_handler->getFileName( $this );
      try
      {
         // read only if exist!!
         return \is_file( $file ) ? \file_get_contents( $file ) : NULL;
      }
      catch ( \Exception $e )
      {
         if ( self::$_logger )
         {
            self::$_logger->warn( 'Could not read from file [' . $file . ']: ' . $e->getMessage() );
         }
         else
         {
            \error_log( 'Could not read from file [' . $file . ']: ' . $e->getMessage() );
         }
         return NULL;
      }
   }

}

//__END_OF_FILE__
