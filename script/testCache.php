<?php

namespace Site;

use lzx\core\ClassLoader;
use lzx\core\Handler;
use lzx\core\Config;
use lzx\core\Logger;
use lzx\core\MySQL;
use lzx\core\Mailer;
use lzx\html\Template;
use lzx\core\Cache;
use site\dataobject\User;

mb_internal_encoding("UTF-8");

// note: cache path in php and nginx are using servername
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';

$domain = 'houstonbbs.com';
$siteDir = dirname(__DIR__);
$path = [
    'lzx' => $siteDir . '/lzx',
    'root' => $siteDir,
    'log' => $siteDir . '/logs',
    'theme' => $siteDir . '/themes',
    'backup' => $siteDir . '/backup',
];

require_once $path['lzx'] . '/Core/ClassLoader.php';
$loader = ClassLoader::getInstance();
$loader->registerNamespace('lzx', $path['lzx']);
$loader->registerNamespace(__NAMESPACE__, $siteDir);

$logger = Logger::getInstance($path['log']);
$logger->userAgent = 'cli';

Handler::$logger = $logger;
// set ErrorHandler
Handler::setErrorHandler();
// set ExceptionHandler
Handler::setExceptionHandler();

// load site config and class config
$config = Config::getInstance($siteDir . '/config.php');
$config->domain = $domain;
$config->mail->domain = $domain;
$task = $argv[1];

$func = __NAMESPACE__ . '\do_' . $task;

if (!function_exists($func)) {
    $logger->info('CRON JOB: wrong action : ' . $task);
    exit;
}

$func($path, $logger, $config);

function do_cache($path, $logger, $config)
{
    $cacheKey = 'recentActivities';
    $cache = Cache::getInstance($config->cache_path, 'pc', 'member');
    $cache->setLogger($logger);
    $cache->store('t_key', 't_value');

    echo disk_free_space($config->cache_path);
    if (disk_free_space($config->cache_path) < 1024) {
        $logger->info("no free space left");
        echo '1';
        $cache->clearAllCache();
    }
    echo '2';
    $cache->clearAllCache();
}
