<?php

namespace site;

use site\CronApp;

require_once __DIR__ . '/CronApp.php';
$app = new CronApp();
$app->run( $argc, $argv );

//_END_OF_FILE
