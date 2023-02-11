<?php

declare(strict_types=1);

namespace site\handler\api\stat;

use lzx\html\Template;
use site\Service;
use site\dbobject\Node;
use site\dbobject\User;

class Handler extends Service
{

    public function get(): void
    {
        $node = new Node();
        $r = $node->getNodeStat(self::$city->tidForum);

        $user = new User();
        $u = $user->getUserStat($this->request->timestamp - 300, self::$city->id);

        $uids = $this->session->getOnlineUids();
        $u['onlineCount'] = count($uids);

        $guestCount = count(array_filter($uids, function ($uid) {
            return $uid == 0;
        }));
        $u['onlineGuestCount'] = $guestCount;

        $uids = array_filter($uids, function ($uid) {
            return $uid != 0;
        });
        $u['onlineUserCount'] = count($uids);

        $onlineUsers = $uids ? $user->getUsernames($uids) : [];
        shuffle($onlineUsers);
        $u['onlineUsers'] = implode(', ', $onlineUsers);


        // make some fake guest :)
        if ($u['onlineCount'] > 1) {
            $ratio = self::$city->id == 1 ? 1.2 : 1.5;
            $u['onlineCount'] = ceil($u['onlineCount'] * $ratio);
            $u['onlineGuestCount'] = $u['onlineCount'] - $u['onlineUserCount'];
        }
        $this->json(array_merge($r, $u));
    }
}
