<?php

namespace site;

use site\WebApp;

if ( \PHP_SAPI === 'cli' )
{
    $_SERVER['HTTP_HOST'] = 'www.longzox.com';
    $opts = \getopt( 'l::' );
    $_SERVER['REQUEST_URI'] = $opts['l'] ? $opts['l'] : '/';
}

require_once __DIR__ . '/WebApp.php';

$app = new WebApp();
$app->run();

//__END_OF_FILE__