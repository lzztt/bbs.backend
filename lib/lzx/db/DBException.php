<?php

namespace lzx\db;

class DBException extends \Exception
{

   protected $db;
   protected $sql;

   public function __construct( $message = NULL, $db = NULL, $sql = NULL, $code = 11, $previous = NULL )
   {
      $message = '[DB] ' . ($db ? $db : 'NULL')
            . ' [SQL] ' . ($sql ? $sql : 'NULL')
            . ' [MSG] ' . ($message ? $message : 'NULL');
      parent::__construct( $message, $code, $previous );
   }

}

//__END_OF_FILE__