<?php

declare(strict_types=1);

namespace site\handler\api\report;

use lzx\core\Mailer;
use lzx\exception\ErrorMessage;
use site\Service;
use site\dbobject\Comment;
use site\dbobject\Node;
use site\dbobject\NodeComplain;
use site\dbobject\User;

class Handler extends Service
{
    public function post(): void
    {
        $this->validateUser();

        $nid = (int) $this->request->data['nodeId'];
        $reason = $this->request->data['reason'];

        $complain = new NodeComplain();
        $complain->nid = $nid;
        $complain->reporterUid = $this->request->uid;

        $complain->load();
        if ($complain->exists()) {
            throw new ErrorMessage('您已经举报过此帖，不能重复举报');
        }

        $node = new Node($nid);
        if (!$node->exists()) {
            throw new ErrorMessage('被举报的帖子不存在');
        }

        if ($node->uid == $this->request->uid) {
            throw new ErrorMessage('您不能举报自己的帖子');
        }

        if ($node->status > 0) {
            $spammer = new User($node->uid);

            if ($spammer->exists() && $spammer->status > 0) {
                $reporter = new User($this->request->uid);

                if ($reporter->status > 0) {
                    $complain->uid = $node->uid;
                    $complain->weight = $reporter->contribution;
                    $complain->time = $this->request->timestamp;
                    $complain->reason = $reason;
                    $complain->status = 1;
                    $complain->add();

                    $comment = new Comment();
                    $comment->nid = $nid;
                    $arr = $comment->getList('id,body', 1);
                    $body = $arr[0]['body'];

                    $title = '举报';
                    if ($reporter->contribution > 0 && ($spammer->contribution < 2 || (strpos($body, 'http') && $spammer->contribution < 18) !== false)) {
                        // check complains
                        $complain = new NodeComplain();
                        $complain->where('uid', $node->uid, '=');
                        $complain->where('status', 1, '=');
                        $complain->where('weight', 0, '>');
                        $reporterUids = array_unique(array_column($complain->getList('reporterUid'), 'reporterUid'));
                        if (count($reporterUids) >= 3) {
                            $user = new User();
                            $user->where('id', $reporterUids, 'IN');
                            $reporterIps = array_unique(array_column($user->getList('lastAccessIp'), 'lastAccessIp'));
                        }
                        if (!empty($reporterIps) && count($reporterIps) >= 3) {
                            $spammer->delete();
                            foreach ($spammer->getAllNodeIDs() as $nid) {
                                $this->getIndependentCache('/node/' . $nid)->delete();
                            }
                            $title = '删除被举报用户';
                        }
                    }

                    // send notification
                    $mailer = new Mailer('complain');
                    $mailer->setTo('ikki3355@gmail.com');
                    $mailer->setSubject($title . ': ' . $spammer->username . ' <' . $spammer->email . '>');
                    $mailer->setBody(print_r([
                        'spammer'  => [
                            'id'         => 'https://' . $this->request->domain . '/user/' . $node->uid,
                            'username' => $spammer->username,
                            'email'     => $spammer->email,
                            'city'      => self::getLocationFromIp($spammer->lastAccessIp),
                            'contribution'    => $spammer->contribution,
                            'register' => date(DATE_COOKIE, $spammer->createTime)
                        ],
                        'node'      => [
                            'id'     => 'https://' . $this->request->domain . '/node/' . $nid,
                            'title' => $node->title,
                            'body'  => $body,
                        ],
                        'reporter' => [
                            'id'         => 'https://' . $this->request->domain . '/user/' . $reporter->id,
                            'username' => $reporter->username,
                            'email'     => $reporter->email,
                            'city'      => self::getLocationFromIp($this->request->ip),
                            'contribution'    => $reporter->contribution,
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
