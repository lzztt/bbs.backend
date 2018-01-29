<?php declare(strict_types=1);

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

        $uid = (int) $this->request->post['uid'];
        $nid = (int) $this->request->post['nid'];
        $reason = $this->request->post['reason'];

        if ($uid == $this->request->uid) {
            throw new ErrorMessage('您不能举报自己的帖子');
        }

        $complain = new NodeComplain();
        $complain->nid = $nid;
        $complain->reporterUid = $this->request->uid;

        $complain->load();
        if ($complain->exists()) {
            throw new ErrorMessage('您已经举报过此帖，不能重复举报');
        }

        $node = new Node();
        $node->id = $nid;
        $node->uid = $uid;
        $node->load();
        if (!$node->exists()) {
            throw new ErrorMessage('被举报的帖子不存在');
        }

        if ($node->status > 0) {
            $spammer = new User($uid);

            if ($spammer->exists() && $spammer->status > 0) {
                $reporter = new User($this->request->uid);

                if ($reporter->status > 0) {
                    $complain->uid = $uid;
                    $complain->weight = $reporter->points;
                    $complain->time = $this->request->timestamp;
                    $complain->reason = $reason;
                    $complain->status = 1;
                    $complain->add();

                    $comment = new Comment();
                    $comment->nid = $nid;
                    $arr = $comment->getList('id,body', 1);
                    $body = $arr[0]['body'];

                    $title = '举报';
                    if ($reporter->points > 0 && ($spammer->points < 2 || (strpos($body, 'http') && $spammer->points < 18) !== false)) {
                        // check complains
                        $complain = new NodeComplain();
                        $complain->where('uid', $uid, '=');
                        $complain->where('status', 1, '=');
                        $complain->where('weight', 0, '>');
                        if ($complain->getCount() >= 3) {
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
                            'id'         => 'https://' . $this->request->domain . '/app/user/' . $uid,
                            'username' => $spammer->username,
                            'email'     => $spammer->email,
                            'city'      => self::getLocationFromIp($spammer->lastAccessIp),
                            'points'    => $spammer->points,
                            'register' => date(DATE_COOKIE, $spammer->createTime)
                        ],
                        'node'      => [
                            'id'     => 'https://' . $this->request->domain . '/node/' . $nid,
                            'title' => $node->title,
                            'body'  => $body,
                        ],
                        'reporter' => [
                            'id'         => 'https://' . $this->request->domain . '/app/user/' . $reporter->id,
                            'username' => $reporter->username,
                            'email'     => $reporter->email,
                            'city'      => self::getLocationFromIp($this->request->ip),
                            'points'    => $reporter->points,
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
