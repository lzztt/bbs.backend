<?php declare(strict_types=1);

namespace site\handler\comment\edit;

use Exception;
use site\handler\comment\Comment;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Node;
use site\dbobject\Image;

class Handler extends Comment
{
    public function run()
    {
        // edit existing comment
        $cid = (int) $this->args[0];

        if (strlen($this->request->post['body']) < 5) {
            $this->error('Comment body is too short.');
        }

        $comment = new CommentObject($cid, 'nid,uid');
        if ($this->request->uid != 1 && $this->request->uid != $comment->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }
        $comment->body = $this->request->post['body'];
        $comment->lastModifiedTime = $this->request->timestamp;
        try {
            $comment->update('body,lastModifiedTime');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->error($e->getMessage());
        }

        // FORUM comments images
        if ($this->request->post['update_file']) {
            $files = is_array($this->request->post['files']) ? $this->request->post['files'] : [];
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($files, $this->config->path['file'], $comment->nid, $cid);
            $this->getIndependentCache('imageSlider')->delete();
        }

        $this->getCacheEvent('NodeUpdate', $comment->nid)->trigger();

        $this->pageRedirect($this->request->referer);
    }
}
