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
use site\dbobject\NodeYellowPage;
use site\gen\theme\roselife\EditorBbcodeYp;
use site\handler\node\Node;

class Handler extends Node
{

    public function run(): void
    {
        $this->response->type = Response::JSON;

        $this->validateUser();

        list($nid, $type) = $this->getNodeType();
        switch ($type) {
            case self::FORUM_TOPIC:
                $this->editForumTopic($nid);
                break;
            case self::YELLOW_PAGE:
                $this->editYellowPage($nid);
                break;
        }
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

        if ($this->request->uid != 1 && $this->request->uid != $node->uid) {
            $this->logger->warning('wrong action : uid = ' . $this->request->uid);
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
        $comment->update();

        $files = $this->getFormFiles();

        $file = new Image();
        $file->cityId = self::$city->id;
        $file->updateFileList($files, $this->config->path['file'], $nid, $comment->id);

        $this->getCacheEvent('ImageUpdate')->trigger();
        $this->getCacheEvent('NodeUpdate', $nid)->trigger();

        throw new Redirect($this->request->referer);
    }

    private function editYellowPage(int $nid): void
    {
        $node = new NodeObject($nid, 'uid,status');

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if ($this->request->uid != 1 && $this->request->uid != $node->uid) {
            $this->logger->warning('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }

        if (!$this->request->data) {
            $this->response->type = Response::HTML;

            // display edit interface
            $nodeObj = new NodeObject();
            $contents = $nodeObj->getYellowPageNode($nid);

            $comment = new Comment();
            $comment->nid = $nid;
            $arr = $comment->getList('id', 1);

            $comment = new Comment((int) $arr[0]['id'], 'body');

            $image = new Image();
            $image->nid = $nid;
            $image->cid = $comment->id;

            $this->html->setContent(
                (new EditorBbcodeYp())
                    ->setAds($contents['ads'])
                    ->setTitle($contents['title'])
                    ->setAddress($contents['address'])
                    ->setPhone($contents['phone'])
                    ->setEmail($contents['email'])
                    ->setWebsite($contents['website'])
                    ->setBody($comment->boday)
                    ->setFiles($image->getList('id,name,path'))
            );
        } else {
            // save modification
            $node = new NodeObject($nid, 'tid');
            $node->title = $this->request->data['title'];
            $node->lastModifiedTime = $this->request->timestamp;
            $node->update();

            $nodeYP = new NodeYellowPage($nid);
            foreach (array_diff($nodeYP->getProperties(), ['nid', 'adId']) as $k) {
                $nodeYP->$k = !empty($this->request->data[$k]) ? $this->request->data[$k] : null;
            }

            $nodeYP->update();

            $comment = new Comment();
            $comment->nid = $nid;
            $arr = $comment->getList('id', 1);

            $comment = new Comment();
            $comment->id = $arr[0]['id'];
            $comment->body = $this->request->data['body'];
            $comment->lastModifiedTime = $this->request->timestamp;
            $comment->update();

            $files = $this->getFormFiles();

            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($files, $this->config->path['file'], $nid, $comment->id);

            $this->getCacheEvent('NodeUpdate', $nid)->trigger();

            throw new Redirect('/node/' . $nid);
        }
    }
}
