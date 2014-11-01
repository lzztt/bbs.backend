<?php

namespace site;

use lzx\App;
use lzx\db\DB;
use site\Config;
use site\dbobject\User;

require_once __DIR__ . '/../lib/lzx/App.php';

class Script extends App
{

   public function run( $argc, Array $argv )
   {
      $this->loader->registerNamespace( __NAMESPACE__, \dirname( __DIR__ ) . '/server' );

      $config = Config::getInstance();
      $db = DB::getInstance( $config->db );

      $user = new User();
      foreach ( \array_column( $user->getList( 'lastAccessIP' ), 'lastAccessIP', 'id' ) as $id => $ip )
      {
         $geo = \geoip_record_by_name( \long2ip( $ip ) );
         if ( $geo && $geo[ 'city' ] === 'Nanning' )
         {
            echo 'id = ' . $id . ' ip = ' . \long2ip( $ip ) . \PHP_EOL;
         }
      }
   }

}

$app = new Script();
$app->run( $argc, $argv );

//__END_OF_FILE__
