<?php declare(strict_types=1);

namespace site\handler\node\comment;

use Exception;
use lzx\core\Mailer;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        if ($this->request->uid == self::UID_GUEST) {
            $this->pageForbidden();
        }

        list($nid, $type) = $this->getNodeType();
        $method = 'comment' . $type;
        $this->$method($nid);
    }

    private function commentForumTopic(int $nid): void
    {
        // create new comment
        $node = new NodeObject($nid, 'tid,status');

        if (!$node->exists() || $node->status == 0) {
            $this->error('node does not exist.');
        }

        if (!$this->request->post['body']
                || strlen($this->request->post['body']) < 5) {
            $this->error('错误：评论正文字数太少。');
        }

        $user = new User($this->request->uid, 'createTime,points,status');
        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->post['body']);
            }

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->uid = $this->request->uid;
            $comment->tid = $node->tid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->logger->info('SPAMMER FOUND: uid=' . $user->id);
                $user->delete();
                $u = new User();
                $u->lastAccessIp = inet_pton($this->request->ip);
                $users = $u->getList('createTime');
                $deleteAll = true;
                if (sizeof($users) > 0) {
                    // check if we have old users that from this ip
                    foreach ($users as $u) {
                        if ($this->request->timestamp - $u['createTime'] > 2592000) {
                            $deleteAll = false;
                            break;
                        }
                    }

                    if ($deleteAll) {
                        $log = 'SPAMMER FROM IP ' . $this->request->ip . ': uid=';
                        foreach ($users as $u) {
                            $spammer = new User($u['id'], 'id');
                            $spammer->delete();
                            $log = $log . $spammer->id . ' ';
                        }
                        $this->logger->info($log);
                    }
                }
                if (false && $this->config->webmaster) { // turn off spammer email
                    $mailer = new Mailer();
                    $mailer->setSubject('SPAMMER detected and deleted (' . sizeof($users) . ($deleteAll ? ' deleted)' : ' not deleted)'));
                    $mailer->setBody(' --node-- ' . $this->request->post['title'] . PHP_EOL . $this->request->post['body']);
                    $mailer->setTo($this->config->webmaster);
                    $mailer->send();
                }
            }

            $this->logger->warn($e->getMessage() . PHP_EOL . ' --comment-- ' . $this->request->post['body']);
            $this->error($e->getMessage());
        }

        if ($this->request->post['files']) {
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($this->request->post['files'], $this->config->path['file'], $nid, $comment->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('ForumComment')->trigger();
        $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();

        if (in_array($nid, $node->getHotForumTopicNIDs(self::$city->tidForum, 15, $this->request->timestamp - 604800))) {
            $this->getIndependentCache('hotForumTopics')->delete();
        }

        $this->pageRedirect('/node/' . $nid . '?p=l#comment' . $comment->id);
    }

    private function commentYellowPage(int $nid): void
    {
        // create new comment
        $node = new NodeObject($nid, 'status');

        if (!$node->exists() || $node->status == 0) {
            $this->error('node does not exist.');
        }

        if (strlen($this->request->post['body']) < 5) {
            $this->error('错误：评论正文字数太少。');
        }

        $user = new User($this->request->uid, 'createTime,points,status');
        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->post['body']);
            }

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->logger->info('SPAMMER FOUND: uid=' . $user->id);
                $user->delete();
                $u = new User();
                $u->lastAccessIp = inet_pton($this->request->ip);
                $users = $u->getList('createTime');
                $deleteAll = true;
                if (sizeof($users) > 0) {
                    // check if we have old users that from this ip
                    foreach ($users as $u) {
                        if ($this->request->timestamp - $u['createTime'] > 2592000) {
                            $deleteAll = false;
                            break;
                        }
                    }

                    if ($deleteAll) {
                        $log = 'SPAMMER FROM IP ' . $this->request->ip . ': uid=';
                        foreach ($users as $u) {
                            $spammer = new User($u['id'], 'id');
                            $spammer->delete();
                            $log = $log . $spammer->id . ' ';
                        }
                        $this->logger->info($log);
                    }
                }
                if ($this->config->webmaster) {
                    $mailer = new Mailer();
                    $mailer->setSubject('SPAMMER detected and deleted (' . sizeof($users) . ($deleteAll ? ' deleted)' : ' not deleted)'));
                    $mailer->setBody(' --node-- ' . $this->request->post['title'] . PHP_EOL . $this->request->post['body']);
                    $mailer->setTo($this->config->webmaster);
                    $mailer->send();
                }
            }

            $this->logger->warn($e->getMessage() . PHP_EOL . ' --comment-- ' . $this->request->post['body']);
            $this->error($e->getMessage());
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('YellowPageComment')->trigger();

        $this->pageRedirect('/node/' . $nid . '?p=l#commentomment' . $comment->id);
    }
}
