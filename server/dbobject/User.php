<?php declare(strict_types=1);

namespace site\dbobject;

use Exception;
use lzx\db\DB;
use lzx\db\DBObject;

class User extends DBObject
{
    const HASH_ALGORITHM = PASSWORD_ARGON2I;

    public $id;
    public $username;
    public $password;
    public $email;
    public $wechat;
    public $qq;
    public $website;
    public $firstname;
    public $lastname;
    public $sex;
    public $birthday;
    public $location;
    public $occupation;
    public $interests;
    public $favoriteQuotation;
    public $relationship;
    public $signature;
    public $createTime;
    public $lastAccessTime;
    public $lastAccessIp;
    public $status;
    public $timezone;
    public $avatar;
    public $type;
    public $role;
    public $badge;
    public $points;
    public $cid;

    public function __construct(int $id = 0, string $properties = '')
    {
        $db = DB::getInstance();
        $table = 'users';
        parent::__construct($db, $table, $id, $properties);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, self::HASH_ALGORITHM);
    }

    public function verifyPassword(string $password): bool
    {
        if (!$password) {
            return false;
        }

        if (!$this->password) {
            $this->load('password');
            if (!$this->password) {
                return false;
            }
        }

        switch (strlen($this->password)) {
            case 95:
                $match = password_verify($password, $this->password);
                $rehash = $match && password_needs_rehash($this->password, self::HASH_ALGORITHM);
                break;
            case 32:
                $match = md5('Alex' . $password . 'Tian') === $this->password;
                $rehash = $match;
                break;
            default:
                return false;
        }

        if ($rehash) {
            $this->password = self::hashPassword($password);
            $this->update('password');
        }

        return $match;
    }

    public static function encodeEmail(string $email, int $uid): string
    {
        return base64_encode(implode(' ', [$email, self::hashId($uid)]));
    }

    public static function decodeEmail(string $code): array
    {
        $user = new User();
        list($user->email, $hash) = explode(' ', base64_decode($code));
        $user->load('id');
        return $user->id && self::hashId($user->id) === $hash ? [$user->email, $user->id] : ['', 0];
    }

    private static function hashId(int $id): string
    {
        return strrev(substr(hash('md5', (string) $id), -8));
    }

    public function loginWithEmail(string $email, string $password): bool
    {
        $this->email = $email;
        $this->load('id,username,status,password');
        if ($this->exists() && $this->password && $this->status == 1) {
            return $this->verifyPassword($password);
        }

        return false;
    }

    public function getUserGroup(): array
    {
        if ($this->id) {
            return array_column($this->call('get_user_group(' . $this->id . ')'), 'name');
        }
    }

    public function delete(): void
    {
        if ($this->id > 1) {
            $this->call('delete_user(' . $this->id . ')');
        }
    }

    public function getAllNodeIDs(): array
    {
        return $this->id > 1 ? array_column($this->call('get_user_node_ids(' . $this->id . ')'), 'nid') : [];
    }

    public function getRecentNodes(int $forumRootID, int $limit): array
    {
        return $this->convertColumnNames($this->call('get_user_recent_nodes("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $this->id . ', ' . $limit . ')'));
    }

    public function getRecentComments(int $forumRootID, int $limit): array
    {
        return $this->convertColumnNames($this->call('get_user_recent_comments("' . implode(',', (new Tag($forumRootID, 'id'))->getLeafTIDs()) . '", ' . $this->id . ', ' . $limit . ')'));
    }

    public function getPrivMsgsCount(string $mailbox = 'inbox'): int
    {
        if ($mailbox == 'new') {
            return intval(array_pop(array_pop($this->call('get_pm_count_new(' . $this->id . ')'))));
        } elseif ($mailbox == 'inbox') {
            return intval(array_pop(array_pop($this->call('get_pm_count_inbox(' . $this->id . ')'))));
        } elseif ($mailbox == 'sent') {
            return intval(array_pop(array_pop($this->call('get_pm_count_sent(' . $this->id . ')'))));
        } else {
            throw new Exception('mailbox not found: ' . $mailbox);
        }
    }

    public function getPrivMsgs(string $type, int $limit, int $offset = 0): array
    {
        $proc = $type !== 'sent' ? 'get_pm_list_inbox_2' : 'get_pm_list_sent_2';
        return $this->convertColumnNames($this->call($proc . '(' . $this->id . ',' . $limit . ',' . $offset . ')'));
    }

    public function getUserStat(int $timestamp, int $cid): array
    {
        $stats = array_pop($this->call('get_user_stat(' . strtotime(date("m/d/Y")) . ',' . $cid . ')'));

        $onlines = $this->call('get_user_online(' . $timestamp . ',' . $cid . ')');

        $users = [];
        $guestCount = 0;
        if (isset($onlines)) {
            foreach ($onlines as $u) {
                if ($u['uid'] > 0) {
                    $users[] = $u['username'];
                } else {
                    $guestCount++;
                }
            }
        }

        return [
            'userCount' => $stats['user_count_total'],
            'userTodayCount' => $stats['user_count_recent'],
            'latestUser' => $stats['latest_user'],
            'onlineUsers' => implode(', ', $users),
            'onlineUserCount' => sizeof($users),
            'onlineGuestCount' => $guestCount,
            'onlineCount' => sizeof($users) + $guestCount
        ];
    }

    public function addBookmark(int $nid): void
    {
        $this->call('bookmark_add(' . $this->id . ',' . $nid . ')');
    }

    public function deleteBookmark(int $nid): void
    {
        $this->call('bookmark_delete(' . $this->id . ',' . $nid . ')');
    }

    public function listBookmark(int $limit, int $offset): array
    {
        return $this->call('bookmark_list(' . $this->id . ',' . $limit . ',' . $offset . ')');
    }

    public function countBookmark(): int
    {
        return (int) array_pop(array_pop($this->call('bookmark_count(' . $this->id . ')')));
    }
}
