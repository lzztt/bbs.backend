<?php

declare(strict_types=1);

namespace site\handler\node\comment;

use Exception;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Redirect;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node as NodeObject;
use site\dbobject\User;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        $this->response->type = Response::JSON;

        $this->validateUser();

        unset($this->request->data['title']);

        list($nid, $type) = $this->getNodeType();
        switch ($type) {
            case self::FORUM_TOPIC:
                $this->commentForumTopic($nid);
                break;
            case self::YELLOW_PAGE:
                $this->commentYellowPage($nid);
                break;
        }
    }

    private function commentForumTopic(int $nid): void
    {
        $node = new NodeObject($nid, 'tid,uid,title,reputation,status');

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if (
            !$this->request->data['body']
            || strlen($this->request->data['body']) < 5
        ) {
            throw new ErrorMessage('错误：评论正文字数太少。');
        }

        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $this->validatePost();
            }
            $this->dedup();
            $this->dedupContent($this->request->data['body']);

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->tid = $node->tid;
            $comment->uid = $this->user->id;
            $comment->body = $this->request->data['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->reportableUntil = $this->request->timestamp + self::ONE_DAY * 3;
            $comment->add();

            $node->lastCommentTime = $this->request->timestamp;
            $node->update();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), ['post' => $this->request->data]);
            throw new ErrorMessage($e->getMessage());
        }

        $files = $this->getFormFiles();

        if ($files) {
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($files, $this->config->path['file'], $nid, $comment->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('ForumComment')->trigger();
        $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();

        $newReputation = floor($node->getCommenterCount($nid) / 3) - $node->reputation;
        if ($newReputation > 0) {
            $node->reputation = $node->reputation + $newReputation;
            $node->update();
            $user = new User($node->uid, 'reputation');
            $user->reputation += $newReputation;
            $user->update();
            $this->sendMessage($user->id, '您获得了' . $newReputation . '个声望点，感谢发布热门话题：' . $node->title);
        }

        throw new Redirect('/node/' . $nid . '?p=l#comment' . $comment->id);
    }

    private function commentYellowPage(int $nid): void
    {
        // create new comment
        $node = new NodeObject($nid, 'status');

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if (strlen($this->request->data['body']) < 5) {
            throw new ErrorMessage('错误：评论正文字数太少。');
        }

        try {
            $this->validatePost();
            $this->dedup();
            $this->dedupContent($this->request->data['body']);

            $comment = new Comment();
            $comment->nid = $nid;
            $comment->uid = $this->user->id;
            $comment->body = $this->request->data['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), ['post' => $this->request->data]);
            throw new ErrorMessage($e->getMessage());
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('YellowPageComment')->trigger();

        throw new Redirect('/node/' . $nid . '?p=l#commentomment' . $comment->id);
    }
}
