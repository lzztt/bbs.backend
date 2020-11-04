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

        $alexaCache = $this->getIndependentCache('alexa');
        $alexa = $alexaCache->getData();
        if (!$alexa) {
            $alexa = $this->getAlexa(self::$city->domain);
            if ($alexa) {
                $alexaCache->setData(Template::fromStr($alexa));
            }
        } else {
            $alexa = (string) $alexa;
        }

        $r['alexa'] = $alexa;

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

        $u['onlineUsers'] = $uids ? implode(', ', $user->getUsernames($uids)) : '';


        // make some fake guest :)
        if ($u['onlineCount'] > 1) {
            $ratio = self::$city->id == 1 ? 1.2 : 1.5;
            $u['onlineCount'] = ceil($u['onlineCount'] * $ratio);
            $u['onlineGuestCount'] = $u['onlineCount'] - $u['onlineUserCount'];
        }
        $this->json(array_merge($r, $u));
    }

    private function getAlexa(string $domain): string
    {
        $data = self::curlGet('http://data.alexa.com/data?cli=10&dat=s&url=' . $domain);

        if ($data) {
            preg_match('#<POPULARITY URL="(.*?)" TEXT="([0-9]+){1,}"#si', $data, $p);
            if ($p[2]) {
                $rank = number_format(intval($p[2]));
                return $this->getSiteName() . '最近三个月平均访问量<a target="_blank" href="https://www.alexa.com/siteinfo/' . $domain . '">Alexa排名</a>:<br><a target="_blank" href="https://www.alexa.com/siteinfo/' . $domain . '">第 <b>' . $rank . '</b> 位</a> (更新时间: ' . date('m/d/Y H:i:s T', intval($_SERVER['REQUEST_TIME'])) . ')';
            }
        }

        $this->logger->warning('Get Alexa Rank Error');
        return '';
    }
}
