<?php

namespace lzx\core;

use lzx\core\Session;
use lzx\db\DB;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

class SessionDB extends Session
{

    private $_db;
    private $_table;

    // CLASS FUNCTIONS
    public function __construct( DB $db, $table )
    {
        $this->_db = $db;
        $this->_table = $table;

        \session_set_save_handler( array($this, 'open'), array($this, 'close'), array($this, 'read'), array($this, 'write'), array($this, 'destroy'), array($this, 'gc') );
        \session_start();

        if ( !isset( $this->uid ) )
        {
            $this->uid = 0;
        }
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
        $res = $this->_db->val( 'SELECT data FROM ' . $this->_table . ' WHERE id = ' . $this->_db->str( $sid ) . ' LIMIT 1' );
        return $res ? $res : '';
    }

    public function write( $sid, $data )
    {
        $timestamp = (int) $_SERVER['REQUEST_TIME'];
        $this->_db->insert( 'INSERT INTO ' . $this->_table . ' (id,data,mtime,uid) VALUES (' . $this->_db->str( $sid ) . ', ' . $this->_db->str( $data ) . ',' . $timestamp . ',' . $this->uid . ')' .
            ' ON DUPLICATE KEY UPDATE data = VALUES(data), mtime = VALUES(mtime), uid = VALUES(uid)' );
        return TRUE;
    }

    public function destroy( $sid )
    {
        $this->_db->delete( 'DELETE FROM ' . $this->_table . ' WHERE id = ' . $this->_db->str( $sid ) . ' LIMIT 1' );
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