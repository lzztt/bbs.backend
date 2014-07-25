<?php

namespace site;

use site\Cache;

class SegmentCache extends Cache
{

   static public $path = '/tmp/www.houstonbbs.com/private';
   static protected $_format = '.txt';

   /**
    * fetch segment data from cache
    * @return boolean
    */
   public function fetch()
   {
      if ( !self::$status )
      {
         return FALSE;
      }

      if ( $this->_data )
      {
         return $this->_data;
      }

      return $this->_fetchFromFile();
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
               // save self
               $this->_writeDataFile( $this->_data );

               // link to current parent nodes
               $this->_linkParents();
            }
         }

         // delete(flush) child cache nodes
         $this->_deleteChildren();
      }
   }

   public function _fetchFromFile()
   {
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

}

//__END_OF_FILE__
