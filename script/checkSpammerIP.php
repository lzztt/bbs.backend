<?php

namespace site;

use lzx\App;
use lzx\db\DB;
use site\Config;
use site\dbobject\User;

require_once __DIR__ . '/../lib/lzx/App.php';

class Script extends App
{
    public function run($argc, array $argv)
    {
        $this->loader->registerNamespace(__NAMESPACE__, dirname(__DIR__) . '/server');

        $config = Config::getInstance();
        $db = DB::getInstance($config->db);

        $users = $db->query('select status, id, username, email,inet6_ntoa(last_access_ip) as ip from users where last_access_ip is not null');
        foreach ($users as $u) {
            $geo = geoip_record_by_name($u['ip']);
            $u['city'] = ($geo && $geo['city'] ? $geo['city'] : 'NULL');
            echo implode("\t", $u) . \PHP_EOL;
        }
    }
}

$app = new Script();
$app->run($argc, $argv);
