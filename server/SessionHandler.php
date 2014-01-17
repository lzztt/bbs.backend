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
        $res = $this->_db->call( 'read_session("' . $sid . '",' . $_SERVER['REQUEST_TIME'] . ')' );
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
            $this->_db->call( 'write_session("' . $sid . '","' . $data . '",' . $_SESSION['uid'] . ')' );
        }
        //$this->_db->insert( 'INSERT INTO ' . $this->_table . ' (id,data,mtime,uid) VALUES (' . $this->_db->str( $sid ) . ', ' . $this->_db->str( $data ) . ',' . $timestamp . ',' . $this->uid . ')' .
        //' ON DUPLICATE KEY UPDATE data = VALUES(data), mtime = VALUES(mtime), uid = VALUES(uid)' );
        return TRUE;
    }

    public function destroy( $sid )
    {
        $this->_db->call( 'delete_session("' . $sid . '")' );
        //$this->_db->delete( 'DELETE FROM ' . $this->_table . ' WHERE id = ' . $this->_db->str( $sid ) . ' LIMIT 1' );
        return TRUE;
    }

    public function gc( $maxlifetime )
    {
        // will do garbage collection through cron job
        //$this->_db->query('DELETE FROM ' . $this->_table . ' WHERE mtime < ' . (TIMESTAMP - $maxlifetime));
        return TRUE;
    }

}

//__END_OF_FILE__