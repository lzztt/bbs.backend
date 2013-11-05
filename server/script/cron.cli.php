<?php

namespace site;

use site\CronApp;

$_SERVERDIR = \dirname( __DIR__ );

$_LZXROOT = \dirname( $_SERVERDIR ) . '/lib/lzx';
require_once __DIR__ . '/CronApp.php';

$app = new CronApp( $_SERVERDIR . '/config.php', array( __NAMESPACE__ => $_SERVERDIR ) );

$app->run( $argc, $argv );

//_END_OF_FILE
