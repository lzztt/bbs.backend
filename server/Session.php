<?php declare(strict_types=1);

namespace site;

use site\dbobject\Session as SessionObj;

class Session
{
    const SID_NAME = 'LZXSID';
    const JSON_OPTIONS = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
    const DEFAULT_DATA = [
        'id' => '',
        'data' => [],
        'uid' => 0,
        'cid' => 0,
        'atime' => 0,
        'crc' => 0,
    ];

    private $crc;
    private $current = [];
    private $original = [];

    public static function getInstance(bool $useDb = true): Session
    {
        static $instance;

        if (!$instance) {
            $instance = new self($useDb);
        }

        return $instance;
    }

    private function __construct(bool $useDb)
    {
        if (!$useDb) {
            $this->current = self::DEFAULT_DATA;
            return;
        }

        $this->crc = crc32($_SERVER['HTTP_USER_AGENT']);
        if (!$this->loadDbSession()) {
            $this->startNewSession();
        }
    }

    private function loadDbSession(): bool
    {
        $sid = empty($_COOKIE[self::SID_NAME]) ? '' : $_COOKIE[self::SID_NAME];

        if (!$sid) {
            return false;
        }

        $session = new SessionObj($sid);
        if (!$session->exists() || $this->crc !== $session->crc) {
            return false;
        }

        $this->original = $session->toArray();
        $this->original['data'] = self::decodeData($session->data);

        $this->current = $this->original;
        $this->current['atime'] = (int) $_SERVER['REQUEST_TIME'];

        return true;
    }

    private function startNewSession(): void
    {
        $this->current = array_merge(self::DEFAULT_DATA, [
            'id' => bin2hex(random_bytes(8)),
            'atime' => (int) $_SERVER['REQUEST_TIME'],
            'crc' => $this->crc,
        ]);

        setcookie(self::SID_NAME, $this->current['id'], ($this->current['atime'] + 2592000), '/', '.' . implode('.', array_slice(explode('.', $_SERVER['SERVER_NAME']), -2)));
    }

    private static function encodeData(array $data): string
    {
        return $data ? json_encode($data, self::JSON_OPTIONS) : '';
    }

    private static function decodeData(string $data): array
    {
        if (!$data) {
            return [];
        }

        $array = json_decode($data, true);
        return is_array($array) ? $array : [];
    }

    final public function get(string $name)
    {
        if (in_array($name, ['uid', 'cid'])) {
            return $this->current[$name];
        }

        return $this->current['data'][$name];
    }

    final public function set(string $name, $value): void
    {
        if (in_array($name, ['uid', 'cid'])) {
            $this->current[$name] = (int) $value;
            return;
        }

        if (is_null($value)) {
            unset($this->current['data'][$name]);
        } else {
            $this->current['data'][$name] = $value;
        }
    }

    public function id(): string
    {
        return $this->current['id'];
    }

    public function clear(): void
    {
        $this->current['uid'] = 0;
        $this->current['data'] = [];
    }

    public function close(): void
    {
        if (!$this->current['id']) {
            return;
        }

        if (!$this->original || $this->current['id'] !== $this->original['id']) {
            $this->insertDbSession();
        } else {
            $this->updateDbSession();
        }
    }

    private function insertDbSession(): void
    {
        $session = new SessionObj();
        $insert = $this->current;
        $insert['data'] = self::encodeData($this->current['data']);
        $session->fromArray($insert);
        $session->add();
    }

    private function updateDbSession(): void
    {
        $update = [];
        if ($this->current['uid'] !== $this->original['uid']) {
            $update['uid'] = $this->current['uid'];
        }
        if ($this->current['cid'] !== $this->original['cid']) {
            $update['cid'] = $this->current['cid'];
        }
        if ($this->current['data'] !== $this->original['data']) {
            $update['data'] = self::encodeData($this->current['data']);
        }
        if ($this->current['atime'] - $this->original['atime'] > 600) {
            $update['atime'] = $this->current['atime'];
        }

        if ($update) {
            $session = new SessionObj();
            $session->id = $this->current['id'];
            $session->fromArray($update);
            $session->update();
        }
    }
}
