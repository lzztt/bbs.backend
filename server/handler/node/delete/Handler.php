<?php

declare(strict_types=1);

namespace site\handler\node\delete;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Node as NodeObject;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        $this->validateUser();

        list($nid, $type) = $this->getNodeType();
        switch ($type) {
            case self::FORUM_TOPIC:
                $this->deleteForumTopic($nid);
                break;
            case self::YELLOW_PAGE:
                $this->deleteYellowPage($nid);
                break;
        }
    }

    private function deleteForumTopic(int $nid): void
    {
        $node = new NodeObject($nid, 'uid,tid,status');

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if ($this->user->id !== self::UID_ADMIN && $this->user->id !== $node->uid) {
            $this->logger->warning('wrong action : uid = ' . $this->user->id);
            throw new Forbidden();
        }

        $node->status = 0;
        $node->update('status');

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();

        throw new Redirect('/forum/' . $node->tid);
    }

    private function deleteYellowPage(int $nid): void
    {
        if ($this->user->id !== self::UID_ADMIN) {
            $this->logger->warning('wrong action : uid = ' . $this->user->id);
            throw new Forbidden();
        }
        $node = new NodeObject($nid, 'tid,status');
        if ($node->exists() && $node->status > 0) {
            $node->status = 0;
            $node->update('status');
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('YellowPageUpdate', $node->tid)->trigger();

        throw new Redirect('/yp/' . $node->tid);
    }
}
