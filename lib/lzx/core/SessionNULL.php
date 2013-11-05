<?php

namespace lzx\core;

use lzx\core\Session;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

class SessionNULL extends Session
{

   public function __construct()
   {
      $this->uid = 0; // don't start session and DB, use empty $_SESSION array directly
      return;
   }

}

//__END_OF_FILE__