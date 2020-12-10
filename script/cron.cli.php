<?php

declare(strict_types=1);

namespace script;

use site\CronApp;

require_once dirname(__DIR__) . '/vendor/autoload.php';

$_SERVER = array_merge($_SERVER, [
    'HTTP_USER_AGENT' => 'CLI',
    'HTTP_REFERER' => '',
    'SERVER_NAME' => $argv[1],
    'REMOTE_ADDR' => '127.0.0.1',
    'HTTPS' => 'on',
    'SERVER_PROTOCOL' => 'HTTP/2.0',
    'REQUEST_URI' => $argv[2],
    'REQUEST_METHOD' => 'GET',
    'QUERY_STRING' => '',
]);

$app = new CronApp();
$app->run([]);
