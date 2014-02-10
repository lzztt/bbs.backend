<?php

namespace site;

use lzx\db\DB;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

class SessionHandler implements \SessionHandlerInterface
{

    private $_db;
    private $_data = '';

    // CLASS FUNCTIONS
    public function __construct( DB $db )
    {
        $this->_db = $db;
    }

    // SESSION FUNCTIONS

    public function open( $save_path, $session_name )
    {
        return TRUE;
    }

    public function close()
    {
        return TRUE;
    }

    public function read( $sid )
    {
        $res = $this->_db->query( 'CALL read_session(:sid, :time)', [':sid' => $sid, ':time' => $_SERVER['REQUEST_TIME']] );
        if ( \is_array( $res ) && \sizeof( $res ) == 1 )
        {
            $this->_data = \array_pop( \array_pop( $res ) );
        }

        return $this->_data;
    }

    public function write( $sid, $data )
    {
        if ( \sizeof( $_SESSION ) == 1 && empty( $_SESSION['uid'] ) )
        {
            $data = '';
        }

        if ( $data != $this->_data )
        {
            $this->_db->query( 'CALL write_session(:sid, :data, :uid)', [':sid' => $sid, ':data' => $data, ':uid' => $_SESSION['uid']] );
        }
        return TRUE;
    }

    public function destroy( $sid )
    {
        $this->_db->query( 'CALL delete_session(:sid)', [':sid' => $sid] );
        return TRUE;
    }

    public function gc( $maxlifetime )
    {
        // will do garbage collection through cron job
        return TRUE;
    }

}

//__END_OF_FILE__