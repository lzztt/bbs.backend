<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace lzx\core;

/**
 * Description of Cookie
 *
 * @author ikki
 */
class Cookie
{
    protected static $lifetime;
    protected static $path;
    protected static $domain;
    protected $now;
    protected $send = false;
    protected $cookie_dirty = [];

    private function __construct()
    {
        $this->now = (int) $_SERVER['REQUEST_TIME'];
    }

    /**
     * Return the Cookie object
     *
     * @return Cookie
     */
    public static function getInstance()
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self();
        }

        return $instance;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->cookie_dirty)) {
            return $this->cookie_dirty[$key];
        } elseif (array_key_exists($key, $_COOKIE)) {
            return $_COOKIE[$key];
        } else {
            return null;
        }
    }

    public function __set($key, $val)
    {
        $this->cookie_dirty[$key] = $val;
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->cookie_dirty)) {
            return isset($this->cookie_dirty[$key]);
        } elseif (array_key_exists($key, $_COOKIE)) {
            return isset($_COOKIE[$key]);
        } else {
            return false;
        }
    }

    public function __unset($key)
    {
        $this->cookie_dirty[$key] = null;
    }

    public function send()
    {
        if ($this->send) {
            foreach ($this->cookie_dirty as $k => $v) {
                if (isset($v)) {
                    //setcookie( $k, $v, $this->now + self::$lifetime, self::$path, self::$domain );
                    $_COOKIE[$k] = $v;
                } else {
                    if (array_key_exists($k, $_COOKIE)) {
                        //setcookie( $k, '', $this->now - self::$lifetime, self::$path, self::$domain );
                    }
                }
            }
            $this->cookie_dirty = [];
        }
    }

    public static function setParams($lifetime, $path, $domain)
    {
        self::$lifetime = $lifetime;
        self::$path = $path;
        self::$domain = $domain;
    }

    public static function getParams()
    {
        return [
            'lifetime' => self::$lifetime,
            'path' => self::$path,
            'domain' => self::$domain,
        ];
    }

    public function setNoSend()
    {
        $this->send = false;
    }

    public function clear()
    {
        foreach (array_keys($_COOKIE) as $key) {
            unset($this->$key);
        }
    }
}
