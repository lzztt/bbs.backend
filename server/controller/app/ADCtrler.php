<?php

namespace site\controller\app;

use site\controller\App;

class ADCtrler extends App
{

   private $name = 'ad';

   public function getLatestVersion()
   {
      $current = NULL;
      try
      {
         $current = \file_get_contents( $this->config->path[ 'file' ] . '/app/ad.current' );
      }
      catch ( \Exception $ex )
      {
         // ignore and continue
      }

      if ( $current )
      {
         $dir = $this->config->path[ 'file' ] . '/app/' . $current;
         if ( \is_dir( $dir ) )
         {
            return $dir;
         }
      }

      // need to search
      $dirs = \glob( $this->config->path[ 'file' ] . '/app/ad.*', \GLOB_ONLYDIR );
      // not found
      if ( !$dirs )
      {
         $this->pageNotFound();
      }

      $count = \count( $dirs );
      $dir = $count == 1 ? $dirs[ 0 ] : $dirs[ $count - 2 ];

      // cache the latest version
      try
      {
         \file_put_contents( $this->config->path[ 'file' ] . '/app/ad.current', \basename( $dir ) );
      }
      catch ( \Exception $ex )
      {
         // ignore and continue
      }

      return $dir;
   }

   public function run()
   {
      $this->response->setContent( \file_get_contents( $this->getLatestVersion() . '/index.html' ) );
   }

}

//__END_OF_FILE__
