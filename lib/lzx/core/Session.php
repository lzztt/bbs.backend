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
    private $status = false;
    private $sid = null;
    private $handler;

    // CLASS FUNCTIONS

    private function __construct(\SessionHandlerInterface $handler = null)
    {
        if ($handler instanceof \SessionHandlerInterface) {
            session_set_save_handler($handler, false);
            session_start();
            $this->sid = session_id();
            $this->status = true;
            $this->handler = $handler;
            if (!isset($this->uid)) {
                $this->uid = 0;
            }

            if ($this->uid > 0) {
                // validate user agent string
                if ($this->crc32 != $this->crc32()) {
                    // this is really shouldn't happen
                    // notify the user?
                    // clear session for now
                    $this->clear();
                }
            }
        } else {
            $this->uid = 0;
        }
    }

    final public function __get($key)
    {
        if ($key == 'id') {
            return $this->sid;
        }

        return array_key_exists($key, $_SESSION) ? $_SESSION[$key] : null;
    }

    final public function __set($key, $val)
    {
        if ($key == 'id') {
            throw new Exception('trying to set session id, which is read-only.');
        }

        $_SESSION[$key] = $val;
    }

    final public function __isset($key)
    {
        if ($key == 'id') {
            return isset($this->sid);
        }

        return array_key_exists($key, $_SESSION) ? isset($_SESSION[$key]) : false;
    }

    final public function __unset($key)
    {
        if ($key == 'id') {
            throw new Exception('trying to unset session id, which is read-only.');
        }

        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    public function close()
    {
        if ($this->status) {
            if ($this->uid > 0 && !$this->crc32) {
                $this->crc32 = $this->crc32();
            }
            session_write_close();
        }
        $this->clear();
        $this->status = false;
    }

    public function clear()
    {
        $_SESSION = [];
        $this->uid = 0;
    }

    public function regenerateID()
    {
        session_regenerate_id();

        $this->sid = session_id();
    }

    /**
     * Return the Session object
     *
     * @return Session
     */
    public static function getInstance(\SessionHandlerInterface $handler = null)
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self($handler);
        } else {
            throw new \Exception('Session instance already exists, cannot create a new instance with handler');
        }

        return $instance;
    }

    private function crc32()
    {
        return \crc32($_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
    }
}

//__END_OF_FILE__
