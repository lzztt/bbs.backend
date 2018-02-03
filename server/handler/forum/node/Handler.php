<?php declare(strict_types=1);

namespace site\handler\forum\node;

use Exception;
use lzx\exception\ErrorMessage;
use lzx\exception\Redirect;
use site\SpamFilterTrait;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\Node;
use site\handler\forum\Forum;

class Handler extends Forum
{
    use SpamFilterTrait;

    public function run(): void
    {
        if ($this->request->uid == self::UID_GUEST) {
            throw new Redirect('/app/user/login');
        }

        $tag = $this->getTagObj();
        $tagTree = $tag->getTagTree();

        if ($tagTree[$tag->id]['children']) {
            throw new ErrorMessage('Could not post topic in this forum');
        }
        $this->createTopic($tag->id);
    }

    public function createTopic(int $tid): void
    {
        if (!$this->request->data['body']
                || !$this->request->data['title']
                || strlen($this->request->data['body']) < 5
                || strlen($this->request->data['title']) < 5) {
            throw new ErrorMessage('Topic title or body is too short.');
        }

        try {
            $this->validatePost();

            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->request->uid;
            $node->title = $this->request->data['title'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();

            $comment = new Comment();
            $comment->nid = $node->id;
            $comment->tid = $tid;
            $comment->uid = $this->request->uid;
            $comment->body = $this->request->data['body'];
            $comment->createTime = $this->request->timestamp;
            $comment->add();
        } catch (Exception $e) {
            $this->logger->warn($e->getMessage(), ['post' => $this->request->data]);
            throw new ErrorMessage($e->getMessage());
        }

        if ($this->request->data['files']) {
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($this->request->data['files'], $this->config->path['file'], $node->id, $comment->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->getCacheEvent('ForumNode')->trigger();
        $this->getCacheEvent('ForumUpdate', $tid)->trigger();

        throw new Redirect('/node/' . $node->id);
    }
}
