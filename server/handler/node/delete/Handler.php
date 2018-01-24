<?php declare(strict_types=1);

namespace site\handler\node\delete;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Activity;
use site\dbobject\Node as NodeObject;
use site\handler\node\Node;

class Handler extends Node
{
    public function run(): void
    {
        if ($this->request->uid == self::UID_GUEST) {
            throw new Forbidden();
        }

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
        $tags = $node->getTags($nid);

        if (!$node->exists() || $node->status == 0) {
            throw new ErrorMessage('node does not exist.');
        }

        if ($this->request->uid != 1 && $this->request->uid != $node->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }

        $node->status = 0;
        $node->update('status');

        $activity = new Activity($nid, 'nid');
        if ($activity->exists()) {
            $activity->delete();
        }

        $this->getCacheEvent('NodeUpdate', $nid)->trigger();
        $this->getCacheEvent('ForumUpdate', $node->tid)->trigger();

        throw new Redirect('/forum/' . $node->tid);
    }

    private function deleteYellowPage(int $nid): void
    {
        if ($this->request->uid != 1) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
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
