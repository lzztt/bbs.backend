<?php

declare(strict_types=1);

namespace site\handler\api\report;

use lzx\core\Mailer;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\Comment;
use site\dbobject\NodeComplain;
use site\dbobject\SessionEvent;
use site\dbobject\User;

class Handler extends Service
{
    public function get(): void
    {
        if (!$this->args) {
            throw new Forbidden();
        }

        $cids = array_filter(array_map('intval', explode(',', $this->args[0])), function (int $v): bool {
            return $v > 0;
        });

        $complains = [];
        $complain = new NodeComplain();
        foreach ($complain->getCommentComplains($cids) as $r) {
            $cid = (int) $r['cid'];
            $status =  (int) $r['status'];
            $complains[$cid] = [
                'status' => $status
            ];

            $skipAuthor = in_array((int) $r['uid'], [self::UID_ADMIN, $this->user->id]);
            if ($status < 2 && !$skipAuthor) {
                $complain->cid = $cid;
                $complain->reporterUid = $this->user->id;
                $complain->load('time');
                if (!$complain->exists()) {
                    $complains[$cid]['reportableUntil'] = (int) $r['lastReportTime'] + self::ONE_DAY * 3 * (1 + (int) $r['reportCount']);
                } else {
                    $complains[$cid]['myReportTime'] = $complain->time;
                }
            }
        }

        $cids = array_diff($cids, array_keys($complains));
        if ($cids) {
            $comment = new Comment();
            $comment->where('id', $cids, 'IN');
            foreach ($comment->getList('uid,createTime') as $r) {
                $cid = (int) $r['id'];
                $skipAuthor = in_array((int) $r['uid'], [self::UID_ADMIN, $this->user->id]);
                if (!$skipAuthor) {
                    $complains[$cid] = [
                        'reportableUntil' => (int) $r['createTime'] + self::ONE_DAY * 3,
                    ];
                }
            }
        }

        $this->json($complains);
    }

    public function post(): void
    {
        $this->validateUser();

        $cid = (int) $this->request->data['commentId'];
        $reason = $this->request->data['reason'];

        $complain = new NodeComplain();
        $complain->cid = $cid;
        $complain->reporterUid = $this->user->id;

        $complain->load();
        if ($complain->exists()) {
            throw new ErrorMessage('错误：您已经举报过此帖，不能重复举报。');
        }

        $comment = new Comment($cid);
        if (!$comment->exists() || $comment->status !== 1) {
            throw new ErrorMessage('错误：被举报的帖子不存在。');
        }

        if ($comment->uid === $this->user->id) {
            throw new ErrorMessage('错误：您不能举报自己的帖子。');
        }

        $spammer = new User($comment->uid);

        if (!$spammer->exists() || $spammer->status !== 1) {
            throw new ErrorMessage('错误：被举报的用户不存在。');
        }

        $complain->uid = $comment->uid;
        $complain->nid = $comment->nid;
        $complain->cid = $cid;
        $complain->weight = $this->user->reputation;
        $complain->time = $this->request->timestamp;
        $complain->reason = $reason;
        $complain->status = 1;
        $complain->add();

        $title = '举报';

        $complainGroups = $this->getComplains($cid);
        $sessionEvent = new SessionEvent();
        $maxReporterCount = 0;
        foreach ($complainGroups as $key => $uids) {
            if (count($uids) > 2) {
                $uniqueUserCount = self::countUserClusters($sessionEvent->getIps($uids));
                if ($maxReporterCount < $uniqueUserCount) {
                    $maxReporterCount = $uniqueUserCount;
                    $reason = $key;
                }
            }
        }
        if ($maxReporterCount > 2) {
            $days = pow(3, $complain->getViolationCount($spammer->id) + 1);
            $spammer->lockedUntil = max($spammer->lockedUntil, $this->request->timestamp) + self::ONE_DAY * $days;
            $spammer->reputation -= 3;
            $spammer->contribution -= 3;
            $spammer->update('lockedUntil,reputation,contribution');
            $this->logoutUser($spammer->id);
            $title = '封禁';

            $this->updateComplainStatus($cid);

            foreach (array_merge(...array_values($complainGroups)) as $uid) {
                $reporter = new User($uid, 'contribution,status');
                if (!$reporter->exists() || $reporter->status !== 1) {
                    $this->logger->error('reporter user not found: ' . $uid);
                    continue;
                }
                $reporter->contribution += 1;
                $reporter->update('contribution');
                $this->sendMessage(
                    $uid,
                    '举报成功，您获得了1个贡献点，感谢您对良好交流环境的维护！' . PHP_EOL
                        . '处理结果：' . $spammer->username . '被封禁' . $days . '天，违规贴被标记'
                );
            }

            $this->postTopic(
                $spammer->username . '被系统封禁' . $days . '天',
                '原因：[url=/user/' . $spammer->id . ']' . $spammer->username . '[/url]在[url=/node/' . $comment->nid . ']这个帖子[/url]里的发言被多位用户举报。' . PHP_EOL
                    . '类别：'  . $reason . PHP_EOL
                    . '结果：用户被封禁' . $days . '天，用户的声望和贡献各减3点。'
            );
        }

        // send notification
        $mailer = new Mailer('complain');
        $mailer->setTo('ikki3355@gmail.com');
        $mailer->setSubject($title . ' ' . $reason . ': ' . $spammer->username . ' <' . $spammer->email . '>');
        $mailer->setBody(print_r([
            'spammer' => [
                'spammer' => $spammer->username
                    . ' : ' . $spammer->email
                    . ' : ' . 'https://' . $this->request->domain . '/user/' . $spammer->id,
                'city' => self::getLocationFromIp($spammer->lastAccessIp),
                'reputation' => $spammer->reputation,
                'register' => date(DATE_COOKIE, $spammer->createTime)
            ],
            'comment' => [
                'id' => 'https://' . $this->request->domain . '/node/' . $comment->nid,
                'body' => $comment->body,
            ],
            'reporter' => [
                'reporter' => $this->user->username
                    . ' : ' . $this->user->email
                    . ' : ' . 'https://' . $this->request->domain . '/user/' . $this->user->id,
                'city' => self::getLocationFromIp($this->request->ip),
                'reputation' => $this->user->reputation,
                'register' => date(DATE_COOKIE, $this->user->createTime)
            ]
        ], true));
        $mailer->send();

        $this->json();
    }

    private static function countUserClusters(array $user_ip_rows): int
    {
        $uidIpMap = [];
        $ipUidMap = [];
        foreach ($user_ip_rows as $r) {
            if ((int) $r['user_id'] === self::UID_ADMIN) {
                $uidIpMap[$r['user_id']] = [];
                continue;
            }

            if (array_key_exists($r['user_id'], $uidIpMap)) {
                $uidIpMap[$r['user_id']][] = $r['ip'];
            } else {
                $uidIpMap[$r['user_id']] = [$r['ip']];
            }
            if (array_key_exists($r['ip'], $ipUidMap)) {
                $ipUidMap[$r['ip']][] = $r['user_id'];
            } else {
                $ipUidMap[$r['ip']] = [$r['user_id']];
            }
        }

        $uidStatusMap = array_fill_keys(array_keys($uidIpMap), true);
        $count = 0;
        foreach (array_keys($uidStatusMap) as $uid) {
            if ($uidStatusMap[$uid]) {
                $count++;
                $uidStatusMap[$uid] = false;
                // graph dfs
                $ips = $uidIpMap[$uid];
                while ($ips) {
                    $ip = array_pop($ips);
                    foreach ($ipUidMap[$ip] as $u) {
                        if ($uidStatusMap[$u]) {
                            $uidStatusMap[$u] = false;
                            $ips = array_merge($ips, array_diff($uidIpMap[$u], $ips, [$ip]));
                        }
                    }
                }
            }
        }
        return $count;
    }

    private function getComplains(int $cid): array
    {
        $complain = new NodeComplain();
        $complain->where('cid', $cid, '=');
        $complain->where('status', 1, '=');
        $res = [];
        foreach ($complain->getList('reporterUid,reason') as $r) {
            if (array_key_exists($r['reason'], $res)) {
                $res[$r['reason']][] = (int) $r['reporterUid'];
            } else {
                $res[$r['reason']] = [(int) $r['reporterUid']];
            }
        }
        return $res;
    }

    private function updateComplainStatus(int $cid): void
    {
        $complain = new NodeComplain();

        $complain->where('cid', $cid, '=');
        $complain->where('status', 1, '=');

        $complain->status = 2;
        $complain->update('status');
    }
}
