<?php

namespace lzx\core;

/*
 * we don't need to set these constants, because we don't do garbage collection here
  define('SESSION_LIFETIME', COOKIE_LIFETIME + 100);
  ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
  ini_set('session.gc_probability', 0);  //set 0, do gc through daily cron job
 */

/**
 * @property SessionHandlerInterface $handler
 */
class Session
{

    private $_status = FALSE;

    // CLASS FUNCTIONS

    private function __construct( \SessionHandlerInterface $handler = NULL )
    {
        if ( $handler instanceof \SessionHandlerInterface )
        {
            \session_set_save_handler( $handler, FALSE );
            \session_start();
            $this->_status = TRUE;
            if ( !isset( $this->uid ) )
            {
                $this->uid = 0;
            }
        }
        else
        {
            $this->uid = 0;
        }
    }

    final public function __get( $key )
    {
        return \array_key_exists( $key, $_SESSION ) ? $_SESSION[$key] : NULL;
    }

    final public function __set( $key, $val )
    {
        $_SESSION[$key] = $val;
    }

    final public function __isset( $key )
    {
        return \array_key_exists( $key, $_SESSION ) ? isset( $_SESSION[$key] ) : FALSE;
    }

    final public function __unset( $key )
    {
        if ( \array_key_exists( $key, $_SESSION ) )
        {
            unset( $_SESSION[$key] );
        }
    }

    public function close()
    {
        if ( $this->_status )
        {
            \session_write_close();
        }
        $this->clear();
        $this->_status = FALSE;
    }

    public function clear()
    {
        $_SESSION = [];
        $this->uid = 0;
    }

    /**
     * Return the Session object
     *
     * @return Session
     */
    public static function getInstance( \SessionHandlerInterface $handler = NULL )
    {
        static $instance;

        if ( !isset( $instance ) )
        {
            $instance = new self( $handler );
        }
        else
        {
            throw new \Exception( 'Session instance already exists, cannot create a new instance with handler' );
        }

        return $instance;
    }

}

//__END_OF_FILE__