<?php

declare(strict_types=1);

namespace site\handler\forum\node;

use Exception;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Redirect;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node;
use site\handler\forum\Forum;

class Handler extends Forum
{
    public function run(): void
    {
        $this->response->type = Response::JSON;

        $this->validateUser();

        $tag = $this->getTagObj();
        $tagTree = $tag->getTagTree();

        if (!empty($tagTree[$tag->id]['children'])) {
            throw new ErrorMessage('Could not post topic in this forum');
        }
        $this->createTopic($tag->id);
    }

    public function createTopic(int $tid): void
    {
        if (
            !$this->request->data['body']
            || !$this->request->data['title']
            || strlen($this->request->data['body']) < 5
            || strlen($this->request->data['title']) < 5
        ) {
            throw new ErrorMessage('Topic title or body is too short.');
        }

        try {
            $this->validatePost();
            $this->dedup();
            $this->dedupContent($this->request->data['body']);

            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->user->id;
            $node->title = $this->request->data['title'];
            $node->createTime = $this->request->timestamp;
            $node->lastCommentTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->user->id;
            $comment->body = $this->request->data['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->reportableUntil = $this->request->timestamp + self::ONE_DAY * 3;
            $comment->status = 1;
            $comment->add();
        } catch (Exception $e) {
            $this->logger->warning($e->getMessage(), ['post' => $this->request->data]);
            throw new ErrorMessage($e->getMessage());
        }

        $files = $this->getFormFiles();

        if ($files) {
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($files, $this->config->path['file'], $node->id, $comment->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->getCacheEvent('ForumNode')->trigger();
        $this->getCacheEvent('ForumUpdate', $tid)->trigger();

        throw new Redirect('/node/' . $node->id);
    }
}
