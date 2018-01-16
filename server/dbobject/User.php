<?php declare(strict_types=1);

namespace site\dbobject;

use Exception;
use lzx\db\DB;
use lzx\db\DBObject;

class User extends DBObject
{
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

    public function hashPW(string $password): string
    {
        return md5('Alex' . $password . 'Tian');
    }

    public function loginWithEmail(string $email, string $password): bool
    {
        $this->email = $email;
        $this->load('id,username,status,password');
        if ($this->exists() && $this->status == 1) {
            return ($this->password === $this->hashPW($password));
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
