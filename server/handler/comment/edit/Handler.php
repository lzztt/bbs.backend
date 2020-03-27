<?php declare(strict_types=1);

namespace site\handler\comment\edit;

use Exception;
use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use lzx\exception\Redirect;
use site\dbobject\Comment as CommentObject;
use site\dbobject\Image;
use site\handler\comment\Comment;

class Handler extends Comment
{
    public function run(): void
    {
        // edit existing comment
        $cid = (int) $this->args[0];

        if (strlen($this->request->data['body']) < 5) {
            throw new ErrorMessage('Comment body is too short.');
        }

        $comment = new CommentObject($cid, 'nid,uid');
        if ($this->request->uid != 1 && $this->request->uid != $comment->uid) {
            $this->logger->warning('wrong action : uid = ' . $this->request->uid);
            throw new Forbidden();
        }
        $comment->body = $this->request->data['body'];
        $comment->lastModifiedTime = $this->request->timestamp;
        try {
            $comment->update('body,lastModifiedTime');
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            throw new ErrorMessage($e->getMessage());
        }

        // FORUM comments images
        if ($this->request->data['update_file']) {
            $files = is_array($this->request->data['files']) ? $this->request->data['files'] : [];
            $file = new Image();
            $file->cityId = self::$city->id;
            $file->updateFileList($files, $this->config->path['file'], $comment->nid, $cid);
            $this->getIndependentCache('imageSlider')->delete();
        }

        $this->getCacheEvent('NodeUpdate', $comment->nid)->trigger();

        throw new Redirect($this->request->referer);
    }
}
