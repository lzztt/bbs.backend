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
            throw new ErrorMessage('您已经举报过此帖，不能重复举报');
        }

        $comment = new Comment($cid);
        if (!$comment->exists()) {
            throw new ErrorMessage('被举报的帖子不存在');
        }

        if ($comment->uid === $this->user->id) {
            throw new ErrorMessage('您不能举报自己的帖子');
        }

        if ($comment->status === 1) {
            $spammer = new User($comment->uid);

            if ($spammer->exists() && $spammer->status > 0 && $spammer->lockedUntil < $this->request->timestamp) {
                $reporter = $this->user;

                if ($reporter->status > 0) {
                    $complain->uid = $comment->uid;
                    $complain->nid = $comment->nid;
                    $complain->cid = $cid;
                    $complain->weight = $reporter->reputation;
                    $complain->time = $this->request->timestamp;
                    $complain->reason = $reason;
                    $complain->status = 1;
                    $complain->add();

                    $title = '举报';
                    if ($reporter->reputation > 0 && ($spammer->reputation < 2 || (strpos($comment->body, 'http') && $spammer->reputation < 18) !== false)) {
                        // check complains
                        $complain = new NodeComplain();
                        $complain->where('cid', $cid, '=');
                        $complain->where('status', 1, '=');
                        $complain->where('weight', 0, '>');
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
                            $title = '封禁被举报用户';
                        }
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
                            'id' => 'https://' . $this->request->domain . '/user/' . $reporter->id,
                            'username' => $reporter->username,
                            'email' => $reporter->email,
                            'city' => self::getLocationFromIp($this->request->ip),
                            'reputation' => $reporter->reputation,
                            'register' => date(DATE_COOKIE, $reporter->createTime)
                        ]
                    ], true));
                    $mailer->send();
                }
            }
        }

        $this->json();
    }
}
