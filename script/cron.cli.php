<?php declare(strict_types=1);

namespace script;

use script\CronApp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$app = new CronApp();
$app->run($argv);
