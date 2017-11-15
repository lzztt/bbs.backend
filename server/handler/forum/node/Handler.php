<?php declare(strict_types=1);

namespace site\handler\forum\node;

use site\handler\forum\Forum;
use site\dbobject\Node;
use site\dbobject\Image;
use site\dbobject\User;
use lzx\core\Mailer;
use site\dbobject\Comment;

class Handler extends Forum
{
    public function run()
    {
        if ($this->request->uid == self::UID_GUEST) {
            $this->response->pageRedirect('/app/user/login');
            return;
        }

        $tag = $this->getTagObj();
        $tagTree = $tag->getTagTree();

        sizeof($tagTree[$tag->id]['children']) ? $this->error('Could not post topic in this forum') : $this->createTopic($tag->id);
    }

    public function createTopic($tid)
    {
        if (strlen($this->request->post['body']) < 5 || strlen($this->request->post['title']) < 5) {
            $this->error('Topic title or body is too short.');
        }

        $user = new User($this->request->uid, 'createTime,points,status');
        try {
            // validate post
            $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->post['body'], $this->request->post['title']);

            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->request->uid;
            $node->title = $this->request->post['title'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (\Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->logger->info('SPAMMER FOUND: uid=' . $user->id);
                $user->delete();
                $u = new User();
                $u->lastAccessIP = inet_pton($this->request->ip);
                $users = $u->getList('createTime');
                $deleteAll = true;
                if (sizeof($users) > 1) {
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
                            $spammer = new User($u['id'], null);
                            $spammer->delete();
                            $log = $log . $spammer->id . ' ';
                        }
                        $this->logger->info($log);
                    }
                }

                if (false && $this->config->webmaster) { // turn off spammer emails
                    $mailer = new Mailer();
                    $mailer->subject = 'SPAMMER detected and deleted (' . sizeof($users) . ($deleteAll ? ' deleted)' : ' not deleted)');
                    $mailer->body = ' --node-- ' . $this->request->post['title'] . PHP_EOL . $this->request->post['body'];
                    $mailer->to = $this->config->webmaster;
                    $mailer->send();
                }
            }

            $this->logger->error($e->getMessage() . \PHP_EOL . ' --node-- ' . $this->request->post['title'] . PHP_EOL . $this->request->post['body']);
            $this->error($e->getMessage());
        }


        if ($this->request->post['files']) {
            $file = new Image();
            $file->cityID = self::$city->id;
            $file->updateFileList($this->request->post['files'], $this->config->path['file'], $node->id, $comment->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        /*
        $user->points += 3;
        $user->update('points');
         */

        $this->getCacheEvent('ForumNode')->trigger();
        $this->getCacheEvent('ForumUpdate', $tid)->trigger();

        if ($node->tid == 15) {
            $this->getCacheEvent('ImmigrationNode')->trigger();
        }

        $this->pageRedirect('/node/' . $node->id);
    }
}
