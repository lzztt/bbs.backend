<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\core;

/**
 * Description of Cookie
 *
 * @author ikki
 */
class Cookie
{

   protected $now;
   protected static $lifetime;
   protected static $path;
   protected static $domain;

   public function __get($key)
   {
      return \array_key_exists($key, $_COOKIE) ? $_COOKIE[$key] : NULL;
   }

   public function __set($key, $val)
   {
      if ($val === NULL || \strlen($val) == 0)
      {
         $this->__unset($key);
      }
      else
      {
         \setcookie($key, $val, $this->now + self::$lifetime, self::$path, self::$domain);
         $_COOKIE[$key] = $val;
      }
   }

   public function __isset($key)
   {
      return \array_key_exists($key, $_COOKIE) ? isset($_COOKIE[$key]) : FALSE;
   }

   public function __unset($key)
   {
      if (\array_key_exists($key, $_COOKIE))
      {
         \setcookie($key, '', $this->now - self::$lifetime, self::$path, self::$domain);
         unset($_COOKIE[$key]);
      }
   }

   public static function setParams($lifetime, $path, $domain)
   {
      self::$lifetime = $lifetime;
      self::$path = $path;
      self::$domain = $domain;
   }

   public static function getParams()
   {
      return [
         'lifetime' => self::$lifetime,
         'path' => self::$path,
         'domain' => self::$domain,
      ];
   }

   public function clear()
   {
      foreach (\array_keys($_COOKIE) as $key)
      {
         unset($this->$key);
      }
      $sessionName = \session_name();
      $this->$sessionName = session_id();
      $this->uid = 0;
   }

   private function __construct()
   {
      $this->now = (int) $_SERVER['REQUEST_TIME'];
   }

   /**
    * Return the Cookie object
    *
    * @return Cookie
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

}

?>
