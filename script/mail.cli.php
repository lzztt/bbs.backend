<?php

namespace site;

use \lzx\App;
use \lzx\core\MySQL;
use \lzx\core\Mailer;
use \lzx\html\Template;
use \site\MailApp;

// note: cache path in php and nginx are using server_name
$_SERVER['SERVER_NAME'] = 'www.houstonbbs.com';
// $config->domain need http_host
$_SERVER['HTTP_HOST'] = 'www.houstonbbs.com';

$_SERVERDIR = \dirname( __DIR__ );
$_LZXROOT = \dirname( $_SERVERDIR ) . '/lib/lzx';

require_once $_LZXROOT . '/App.php';

class MailApp extends App
{

    public function run( $argc, Array $argv )
    {
        $uid = $argc;
        $db = MySQL::getInstance( $this->config->database, TRUE );
        $users = $db->select( 'SELECT uid, username, email, createTime FROM User WHERE uid > ' . $uid . ' LIMIT 550' );

        if ( \sizeof( $users ) > 0 )
        {
            $mailer = new Mailer();
            $mailer->from = 'care';
            $mailer->subject = '缤纷休斯顿网 祝你新春快乐 马到成功';
            $status = [];
            foreach ( $users as $i => $u )
            {
                $mailer->to = $u['email'];
                $contents = [
                    'username' => $u['username'],
                    'time' => $this->time( $u['createTime'] )
                ];
                Template::setLogger( $this->logger );
                Template::$theme = $this->config->theme;
                Template::$path = $this->path['theme'];
                $mailer->body = new Template( 'mail/newyear', $contents );

                if ( $mailer->send() )
                {
                    $status[] = '(' . $u['uid'] . ', 1)';
                }
                else
                {
                    $status[] = '(' . $u['uid'] . ', 0)';
                    Log::info( 'News Letter Email Sending Error: ' . $u['uid'] );
                }
                if ( $i % 100 == 99 )
                {
                    $db->query( 'INSERT INTO Mail (uid, status) values ' . \implode( ',', $status ) );
                    $status = [];
                }
                sleep( 6 );
            }
            if ( $status )
            {
                $db->query( 'INSERT INTO Mail (uid, status) values ' . \implode( ',', $status ) );
            }
            $last = \array_pop( $users );
            return $last['uid'];
        }
    }

    private function time( $timestamp )
    {
        $intv = \date_diff( new \DateTime( \date( 'now' ) ), new \DateTime( \date( 'Y-m-d H:i:s', $timestamp ) ) );
        $days = $intv->days;
        if ( $days / 365 > 4 )
        {
            return '四年多以来';
        }
        if ( $days / 365 > 3 )
        {
            return '三年多以来';
        }
        if ( $days / 365 > 2 )
        {
            return '两年多以来';
        }
        if ( $days / 365 > 1 )
        {
            return '一年多以来';
        }
        if ( $days / 365 > 0.5 )
        {
            return '半年多以来';
        }
        if ( $days / 30 > 5 )
        {
            return '五个多月来';
        }
        if ( $days / 30 > 4 )
        {
            return '四个多月来';
        }
        if ( $days / 30 > 3 )
        {
            return '三个多月来';
        }
        if ( $days / 30 > 2 )
        {
            return '两个多月来';
        }
        if ( $days / 30 > 1 )
        {
            return '一个多月来';
        }

        return '近期';
    }

}

$flag = '/run/mail/sending';
$lock = '/run/mail/lock';
if ( \file_exists( $flag ) )
{
    if( \file_exists( $lock ) )
    {
        echo 'unable to get mail sending lock, aborting';
        exit(1);
    }
    else
    {
        \touch( $lock );

        $uid = \intval( \file_get_contents( $flag ) );
        $app = new MailApp( $_SERVERDIR . '/config.php', array(__NAMESPACE__ => $_SERVERDIR) );
        $uid_new = \intval( $app->run( $uid, [] ) );
        if ( $uid_new > $uid )
        {
            \file_put_contents( $flag, $uid_new );
        }

        \unlink( $lock );
    }
}


//__END_OF_FILE__
