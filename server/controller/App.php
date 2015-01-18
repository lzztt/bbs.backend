<?php

namespace site\controller;

use site\Controller;

abstract class App extends Controller
{

   protected function _getLatestVersion( $app )
   {
      $current = NULL;
      try
      {
         $current = \file_get_contents( $this->config->path[ 'file' ] . '/app/' . $app . '.current' );
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
      $dirs = \glob( $this->config->path[ 'file' ] . '/app/' . $app . '.*', \GLOB_ONLYDIR );
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
         \file_put_contents( $this->config->path[ 'file' ] . '/app/' . $app . '.current', \basename( $dir ) );
      }
      catch ( \Exception $ex )
      {
         // ignore and continue
      }

      return $dir;
   }

}

//__END_OF_FILE__
