<?php declare(strict_types=1);

namespace site;

use Exception;
use lzx\db\DB;

class Session
{
    private static $cookieName = 'LZXSID';
    private $isNew = false;
    private $db;
    private $sid = null;
    private $uid = 0;
    private $cid = 0;
    private $atime = 0;
    private $data = [];
    private $uidOriginal = 0;
    private $cidOriginal = 0;
    private $dataOriginal = [];

    // id is 15 charactors
    private function __construct(DB $db = null)
    {
        if ($db) {
            if ($_COOKIE[self::$cookieName]) {
                // client has a session id
                $this->sid = $_COOKIE[self::$cookieName];
            } else {
                // client has no session id
                $this->startNewSession();
            }

            $this->db = $db;

            if (!$this->isNew) {
                // load session from database
                $arr = $db->query('SELECT * FROM sessions WHERE id = :id', [':id' => $this->sid]);
                if ($arr) {
                    // validate session's user agent crc checksum
                    if ($this->crc32() === (int) $arr[0]['crc']) {
                        // valid agent
                        $this->uid = (int) $arr[0]['uid'];
                        $this->cid = (int) $arr[0]['cid'];
                        $this->atime = (int) $arr[0]['atime'];

                        $this->uidOriginal = $this->uid;
                        $this->cidOriginal = $this->cid;

                        if ($arr[0]['data']) {
                            $this->data = json_decode($arr[0]['data'], true);

                            if (is_array($this->data)) {
                                $this->dataOriginal = $this->data;
                            } else {
                                $this->data = [];
                            }
                        }
                    } else {
                        // invalid agent. this shouldn't happen!!
                        $this->startNewSession();
                    }
                } else {
                    // no session found in database, start new session
                    $this->startNewSession();
                }
            }
        }
    }

    final public function __get($key)
    {
        return array_key_exists($key, $this->data) ? $this->data[$key] : null;
    }

    final public function __set($key, $val)
    {
        if (is_null($val)) {
            unset($this->$key);
        } else {
            $this->data[$key] = $val;
        }
    }

    final public function __isset($key)
    {
        return array_key_exists($key, $this->data) ? isset($this->data[$key]) : false;
    }

    final public function __unset($key)
    {
        if (array_key_exists($key, $this->data)) {
            unset($this->data[$key]);
        }
    }

    public static function getInstance(DB $db = null): Session
    {
        static $instance;

        if (!isset($instance)) {
            $instance = new self($db);
        } else {
            throw new Exception('Session instance already exists, cannot create a new instance');
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

    public function setCityID($cid): void
    {
        $this->cid = (int) $cid;
    }

    public function getUserID(): int
    {
        return $this->uid;
    }

    public function setUserID($uid): void
    {
        $this->uid = (int) $uid;
    }

    public function clear(): void
    {
        $this->uid = 0;
        $this->data = [];
    }

    public function close(): void
    {
        if ($this->db) {
            if ($this->isNew) {
                // db insert for new session
                $this->db->query('INSERT INTO sessions VALUES (:id, :data, :atime, :uid, :cid, :crc)', [
                    ':id' => $this->sid,
                    ':data' => $this->data ? json_encode($this->data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '',
                    ':atime' => $_SERVER['REQUEST_TIME'],
                    ':uid' => $this->uid,
                    ':cid' => $this->cid,
                    ':crc' => $this->crc32()
                ]);
            } else {
                // db update for existing session
                $fields = [];
                $values = [];

                if ($this->uid != $this->uidOriginal) {
                    $fields[] = 'uid=:uid';
                    $values[':uid'] = $this->uid;
                }

                if ($this->cid != $this->cidOriginal) {
                    $fields[] = 'cid=:cid';
                    $values[':cid'] = $this->cid;
                }

                if ($this->data != $this->dataOriginal) {
                    $fields[] = 'data=:data';
                    $values[':data'] = $this->data ? json_encode($this->data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '';
                }

                // update access timestamp older than 1 minute
                $time = (int) $_SERVER['REQUEST_TIME'];
                if ($time - $this->atime > 60) {
                    $fields[] = 'atime=:atime';
                    $values[':atime'] = $time;
                }

                if ($fields) {
                    $sql = 'UPDATE sessions SET ' . implode($fields, ',') . ' WHERE id = "' . $this->sid . '"';
                    $this->db->query($sql, $values);
                }
            }
        }
    }

    private function startNewSession(): void
    {
        $this->sid = sprintf("%02x", rand(0, 255)) . uniqid();
        setcookie(self::$cookieName, $this->sid, ((int) $_SERVER['REQUEST_TIME'] + 2592000), '/', '.' . implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), -2)));
        $this->isNew = true;
    }

    private function crc32(): int
    {
        return crc32($_SERVER['HTTP_USER_AGENT'] . $this->sid);
    }
}
