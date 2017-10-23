<?php

// remove a user comment from quote

require_once dirname(__DIR__) . '/apps/common.php';

if (isset($argv)) {
     $pass = $argv[1];
     $do = $argv[2];
} else {
     $pass = $_GET['pass'];
     $do = $_GET['do'];
}

require_once ROOT . '/apps/settings.php';
require_once CLS_PATH . 'Log.cls.php';

$func = 'do_' . $do;

$func();

function do_comment()
{
     require_once CLS_PATH . 'MySQL.cls.php';

     $db = MySQL::getInstance();

     $comments = $db->query('SELECT cid,body  FROM `comments` WHERE `body` LIKE \'%[quote="Silverdew"]%\'');
     $nc = [];

    foreach ($comments as $c) {
         $body = preg_replace('/\[quote=(.*)\].*\[\/quote\]/s', '', $c['body']);
#        if (strpos($body, 'quote'))
#            echo $c['body'].'<br>'.$body.'<br><br>';
         echo $c['cid'] . ',';
         $db->query('UPDATE comments SET body = ' . $db->str($body) . ' WHERE cid = ' . $c['cid']);
    }
}
