<?php declare(strict_types=1);

namespace site\handler\node\comment;

use Exception;
use site\SpamFilterTrait;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;
use site\handler\node\Node;

class Handler extends Node
{
    use SpamFilterTrait;

    public function run(): void
    {
        if ($this->request->uid == self::UID_GUEST) {
            $this->pageForbidden();
        }

        unset($this->request->post['title']);

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

        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $this->validatePost();
            }

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->tid = $node->tid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['post' => $this->request->post]);
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
            $this->validatePost();

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->post['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), ['post' => $this->request->post]);
            $this->error($e->getMessage());
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('YellowPageComment')->trigger();

        $this->pageRedirect('/node/' . $nid . '?p=l#commentomment' . $comment->id);
    }
}
