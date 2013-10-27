<?php

namespace lzx\core;

use lzx\core\MySQL;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

class Session
{

   public static $isDummyInstance = FALSE;
   private $_db;

   // CLASS FUNCTIONS

   public function __get($key)
   {
      return \array_key_exists($key, $_SESSION) ? $_SESSION[$key] : NULL;
   }

   public function __set($key, $val)
   {
      $_SESSION[$key] = $val;
   }

   public function __isset($key)
   {
      return \array_key_exists($key, $_SESSION) ? isset($_SESSION[$key]) : FALSE;
   }

   public function __unset($key)
   {
      if (\array_key_exists($key, $_SESSION))
      {
         unset($_SESSION[$key]);
      }
   }

   public function clear()
   {
      $_SESSION = array();
      $this->uid = 0;
   }

   private function __construct()
   {
      // dummy session or DB error! return as ROBOT mode!
      if (self::$isDummyInstance)
      {
         $this->uid = 0; // don't start session and DB, use empty $_SESSION array directly
         return;
      }

      $this->_db = MySQL::getInstance();

      \session_set_save_handler(array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc'));
      \session_start();

      if (!isset($this->uid))
      {
         $this->uid = 0;
      }
   }
   
   /**
    * Return the Session object
    *
    * @return Session
    */
   public static function getInstance()
   {
      static $instance;

      if (!isset($instance))
      {
         $instance = new self();
      }

      return $instance;
   }

   // SESSION FUNCTIONS

   public function open($save_path, $session_name)
   {
      return TRUE;
   }

   public function close()
   {
      return TRUE;
   }

   public function read($sid)
   {
      $res = $this->_db->val('SELECT data FROM sessions WHERE sid = ' . $this->_db->str($sid) . ' LIMIT 1');
      return $res ? $res : '';
   }

   public function write($sid, $data)
   {
      $timestamp = (int) $_SERVER['REQUEST_TIME'];
      $this->_db->query('INSERT INTO sessions (sid,data,mtime,uid) VALUES (' . $this->_db->str($sid) . ', ' . $this->_db->str($data) . ',' . $timestamp . ',' . $this->uid . ')' .
         ' ON DUPLICATE KEY UPDATE data = VALUES(data), mtime = VALUES(mtime), uid = VALUES(uid)');
      return TRUE;
   }

   public function destroy($sid)
   {
      $this->_db->query('DELETE FROM sessions WHERE sid = ' . $this->_db->str($sid) . ' LIMIT 1');
      return TRUE;
   }

   public function gc($maxlifetime)
   {
      // will do garbage collection through cron job
      //$this->_db->query('DELETE FROM sessions WHERE mtime < ' . (TIMESTAMP - $maxlifetime));
      return TRUE;
   }

}

//__END_OF_FILE__