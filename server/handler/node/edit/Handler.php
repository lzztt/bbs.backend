<?php

declare(strict_types=1);

namespace site\handler\node\edit;

use Exception;
use lzx\core\Response;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node as NodeObject;
use site\handler\node\Node;

class Handler extends Node
{

    public function run(): void
    {
        $this->response->type = Response::JSON;

        $this->validateUser();
        $nid = $this->getNodeId();
        $this->editForumTopic($nid);
    }

    private function editForumTopic(int $nid): void
    {
        // edit existing comment
        $node = new NodeObject($nid, 'uid,status');

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if (
            !$this->request->data['body']
            || !$this->request->data['title']
            || strlen($this->request->data['body']) < 5
            || strlen($this->request->data['title']) < 5
        ) {
            throw new ErrorMessage('Topic title or body is too short.');
        }

        if ($this->user->id !== self::UID_ADMIN && $this->user->id !== $node->uid) {
            $this->logger->warning('wrong action : uid = ' . $this->user->id);
            throw new Forbidden();
        }

        $node->title = $this->request->data['title'];
        $node->tid = (int) $this->request->data['tagId'];
        $node->lastModifiedTime = $this->request->timestamp;

        try {
            $this->dedup();

            $node->update();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ErrorMessage($e->getMessage());
        }

        $comment = new Comment();
        $comment->nid = $nid;
        $arr = $comment->getList('id', 1);

        $comment = new Comment();
        $comment->id = $arr[0]['id'];
        $comment->tid = (int) $this->request->data['tagId'];
        $comment->body = $this->request->data['body'];
        $comment->lastModifiedTime = $this->request->timestamp;
        $comment->reportableUntil = $this->request->timestamp + self::ONE_DAY * 3;
        $comment->update();

        $files = $this->getFormFiles();

        $file = new Image();
        $file->cityId = self::$city->id;
        $file->updateFileList($files, $this->config->path['file'], $nid, $comment->id);

        $this->getCacheEvent('ImageUpdate')->trigger();
        $this->getCacheEvent('NodeUpdate', $nid)->trigger();

        throw new Redirect($this->request->referer);
    }
}
