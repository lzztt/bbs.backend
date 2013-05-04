<?php

$config = array(
   //'offline' => TRUE,
   'stage' => 'development',
   //'stage' => 'testing',
   //'stage' => 'production',
   //'cache' => TRUE,
   'database' => array(
      'host' => 'p:localhost',
      'username' => 'web',
      'passwd' => 'Ab663067',
      'dbname' => 'houstonbbs'
   ),
   'cache_path' => '/cache/' . $_SERVER['SERVER_NAME'], //note: nginx webserver also use $server_name as the cache path
   /*
    * set timezone in php.ini
    * 'timezone' => 'America/Chicago',
    */
   'get_keys' => 'p,page,type,tid,nid,nids',
   'lang_default' => 'zh-cn',
   'theme' => 'default',
   'domain' => \implode('.', \array_slice(\explode('.', $_SERVER['HTTP_HOST']), -2)),
   'cookie' => array(
      'lifetime' => 2592000,
   //'path' => '/',
   //'domain' => $config['domain'],
   ),
   'mail' => array(
      'user' => 'no-reply',
   //'domain' => $config['domain'],
   ),
);

// make this file immutable
// root# chattr +i config.php
// just in case we rsync the dev/testing configuration file to production
if ($config['domain'] === 'houstonbbs.com')
{
   $config['stage'] = 'production';
   $config['cache'] = TRUE;
}

if ($_SERVER['REMOTE_ADDR'] != '99.89.57.251' && \substr($_SERVER['REMOTE_ADDR'], 0, 10) != '192.168.1.')
{
   \header('Content-Type: text/html; charset=UTF-8');
   \header($_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");
   exit('404 Not Found :(');
}
//__END_OF_FILE__
