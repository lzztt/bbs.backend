<?php

namespace site;

use site\Cache;

class SegmentCache extends Cache
{

   static public $path = '/tmp/www.houstonbbs.com/private';
   static protected $format = '.txt';

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

      if ( $this->data )
      {
         return $this->data;
      }

      return $this->_fetchFromFile();
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
               // save self
               $this->_writeDataFile( $this->data );

               // link to current parent nodes
               foreach ( $this->parents as $p )
               {
                  $this->_saveMap( $p, $this->key );
               }
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
