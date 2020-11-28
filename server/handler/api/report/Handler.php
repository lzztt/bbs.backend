<?php

declare(strict_types=1);

namespace site\handler\api\report;

use lzx\core\Mailer;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\Comment;
use site\dbobject\NodeComplain;
use site\dbobject\User;

class Handler extends Service
{
    public function get(): void
    {
        if (!$this->args) {
            throw new Forbidden();
        }

        $complain = [];
        $nids = array_filter(array_map('intval', explode(',', $this->args[0])), function (int $v): bool {
            return $v > 0;
        });

        if ($nids) {
            $nc = new NodeComplain();
            $nc->getCommentComplains($nids);

            foreach ($nc->getCommentComplains($nids) as $r) {
                $complain[$r['cid']] = (int) $r['status'];
            }
        }

        $this->json($complain);
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

        if ($spammer->lockedUntil > $this->request->timestamp) {
            throw new ErrorMessage('错误：被举报的用户已被封禁。');
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

        // check complains
        $complain = new NodeComplain();
        $complain->where('cid', $cid, '=');
        $complain->where('status', 1, '=');
        $reporterUids = array_unique(array_column($complain->getList('reporterUid'), 'reporterUid'));
        if (count($reporterUids) >= 3) {
            $user = new User();
            $user->where('id', $reporterUids, 'IN');
            $reporterIps = array_unique(array_column($user->getList('lastAccessIp'), 'lastAccessIp'));
        }
        if (!empty($reporterIps) && count($reporterIps) >= 3) {
            $spammer->lockedUntil = $this->request->timestamp + self::ONE_DAY;
            $spammer->update('lockedUntil');
            $this->logoutUser($spammer->id);
            $title = '封禁';

            $this->updateComplainStatus($cid);
            $this->postTopic($spammer->username . '被系统封禁一天', '原因：' . $reason);
        }

        // send notification
        $mailer = new Mailer('complain');
        $mailer->setTo('ikki3355@gmail.com');
        $mailer->setSubject($title . ': ' . $spammer->username . ' <' . $spammer->email . '>');
        $mailer->setBody(print_r([
            'spammer' => [
                'id' => 'https://' . $this->request->domain . '/user/' . $comment->uid,
                'username' => $spammer->username,
                'email' => $spammer->email,
                'city' => self::getLocationFromIp($spammer->lastAccessIp),
                'reputation' => $spammer->reputation,
                'register' => date(DATE_COOKIE, $spammer->createTime)
            ],
            'comment' => [
                'id' => 'https://' . $this->request->domain . '/node/' . $comment->nid,
                'body' => $comment->body,
            ],
            'reporter' => [
                'id' => 'https://' . $this->request->domain . '/user/' . $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email,
                'city' => self::getLocationFromIp($this->request->ip),
                'reputation' => $this->user->reputation,
                'register' => date(DATE_COOKIE, $this->user->createTime)
            ]
        ], true));
        $mailer->send();

        $this->json();
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
