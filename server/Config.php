<?php

namespace site;

class Config
{

   const STAGE_DEVELOPMENT = 0;
   const STAGE_TESTING = 1;
   const STAGE_PRODUCTION = 2;
   const MODE_OFFLINE = 0;
   const MODE_READONLY = 1;
   const MODE_FULL = 2;

   public $stage;
   public $mode;
   public $cache;
   public $path;
   public $db;
   public $getkeys;
   public $language;
   public $theme;
   public $domain;
   public $webmaster;
   public $image;

   private function __construct()
   {
      $this->stage = self::STAGE_DEVELOPMENT;
      $this->stage = self::STAGE_PRODUCTION;
      $this->mode = self::MODE_FULL;

      $this->path = [
         'server' => __DIR__,
         'language' => __DIR__ . '/language',
         'theme' => __DIR__ . '/theme',
         'site' => \dirname( __DIR__ ),
         'log' => \dirname( __DIR__ ) . '/log',
         'file' => \dirname( __DIR__ ) . '/client',
         'backup' => \dirname( __DIR__ ) . '/backup',
         'cache' => '/tmp/' . $_SERVER[ 'SERVER_NAME' ], //note: nginx webserver also use $server_name as the cache path
      ];
      $this->cache = TRUE;
      $this->db = [
         'dsn' => 'hbbs',
         'user' => 'web',
         'password' => 'Ab663067',
      ];
      $this->getkeys = ['p', 'r', 'u', 'c', 't', 'action' ];
      $this->language = 'zh-cn';
      $this->theme = [
         'default' => 'default',
         'wedding' => 'wedding',
         'wedding2' => 'wedding2',
         'adm' => 'adm',
         'single' => 'single',
         'roselife' => 'roselife'
      ];
      $this->domain = \implode( '.', \array_slice( \explode( '.', $_SERVER[ 'HTTP_HOST' ] ), -2 ) );
      $this->webmaster = 'ikki3355@gmail.com';

      $this->image = [
         'types' => [ \IMAGETYPE_GIF, \IMAGETYPE_PNG, \IMAGETYPE_JPEG ],
         'height' => 960,
         'width' => 600,
         'size' => 5242880
      ];

      // make this file immutable
      // root# chattr +i config.php
      // just in case we rsync the dev/testing configuration file to production
      if ( $this->domain === 'houstonbbs.com' )
      {
         $this->stage = self::STAGE_PRODUCTION;
      }
   }

   /**
    *
    * @staticvar self $instance
    * @return \site\Config
    */
   public static function getInstance()
   {
      static $instance;

      if ( !isset( $instance ) )
      {
         $instance = new self();
      }
      return $instance;
   }

}

/*
 * set timezone in php.ini
 * 'timezone' => 'America/Chicago',
 */
//__END_OF_FILE__
