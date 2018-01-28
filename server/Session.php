<?php declare(strict_types=1);

namespace site;

use site\dbobject\Session as SessionObj;

class Session
{
    const COOKIE_NAME = 'LZXSID';
    private $isNew = false;
    private $sid = '';
    private $uid = 0;
    private $cid = 0;
    private $atime = 0;
    private $data = [];
    private $uidOriginal = 0;
    private $cidOriginal = 0;
    private $dataOriginal = [];

    private function __construct(bool $useDb)
    {
        if (!$useDb) {
            return;
        }

        $this->sid = $_COOKIE[self::COOKIE_NAME];

        if(!$this->loadDbSession()) {
            $this->startNewSession();
        }
    }

    private function loadDbSession(): bool
    {
        if (!$this->sid || $this->isNew) {
            return false;
        }

        $session = new SessionObj($this->sid);
        if (!$session->exists() || $this->crc32() !== $session->crc) {
            return false;
        }

        $this->uid = $session->uid;
        $this->cid = $session->cid;
        $this->atime = $session->atime;

        $this->uidOriginal = $this->uid;
        $this->cidOriginal = $this->cid;

        if ($session->data) {
            $data = json_decode($session->data, true);
            if (is_array($data)) {
                $this->data = $data;
                $this->dataOriginal = $data;
            }
        }

        return true;
    }

    final public function __get(string $key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    final public function __set(string $key, $val)
    {
        if (is_null($val)) {
            unset($this->$key);
        } else {
            $this->data[$key] = $val;
        }
    }

    final public function __isset(string $key)
    {
        return array_key_exists($key, $this->data) ? isset($this->data[$key]) : false;
    }

    final public function __unset(string $key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    public static function getInstance(bool $useDb = true): Session
    {
        static $instance;

        if (!$instance) {
            $instance = new self($useDb);
        }

        return $instance;
    }

    public function getSessionID(): string
    {
        return $this->sid;
    }

    public function getCityID(): int
    {
        return $this->cid;
    }

    public function setCityID(int $cid): void
    {
        $this->cid = $cid;
    }

    public function getUserID(): int
    {
        return $this->uid;
    }

    public function setUserID(int $uid): void
    {
        $this->uid = $uid;
    }

    public function clear(): void
    {
        $this->uid = 0;
        $this->data = [];
    }

    public function close(): void
    {
        if (!$this->sid) {
            return;
        }

        if ($this->isNew) {
            $this->insertDbSession();
        } else {
            $this->updateDbSession();
        }
    }

    private function insertDbSession(): void
    {
        $session = new SessionObj();
        $session->id = $this->sid;
        $session->data = $this->data ? json_encode($this->data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        $session->atime = (int) $_SERVER['REQUEST_TIME'];
        $session->uid = $this->uid;
        $session->cid = $this->cid;
        $session->crc = $this->crc32();
        $session->add();
    }

    private function updateDbSession(): void
    {
        $update = [];
        if ($this->uid != $this->uidOriginal) {
            $update['uid'] = $this->uid;
        }

        if ($this->cid != $this->cidOriginal) {
            $update['cid'] = $this->cid;
        }

        if ($this->data != $this->dataOriginal) {
            $update['data'] = $this->data ? json_encode($this->data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
        }

        $time = (int) $_SERVER['REQUEST_TIME'];
        if ($time - $this->atime > 600) {
            $update['atime'] = $time;
        }

        if ($update) {
            $session = new SessionObj();
            $session->id = $this->sid;
            foreach ($update as $k => $v) {
                $session->$k = $v;
            }
            $session->update();
        }
    }

    private function startNewSession(): void
    {
        $this->sid = sprintf("%02x", rand(0, 255)) . uniqid();
        setcookie(self::COOKIE_NAME, $this->sid, ((int) $_SERVER['REQUEST_TIME'] + 2592000), '/', '.' . implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2)));
        $this->isNew = true;
    }

    private function crc32(): int
    {
        return crc32($_SERVER['HTTP_USER_AGENT'] . $this->sid);
    }
}
