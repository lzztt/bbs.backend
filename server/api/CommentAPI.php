<?php

namespace site\api;

use site\Service;
use site\dbobject\Node;
use site\dbobject\Comment;
use site\dbobject\User;
use site\dbobject\Image;

class CommentAPI extends Service
{
    const COMMENTS_PER_PAGE = 20;

    /**
     * add a new comment
     * uri: /api/comment[?action=post]
     * post: key=<value>
     */
    public function post()
    {
        if (!$this->request->uid || empty($this->request->json)) {
            $this->forbidden();
        }

        if (\strlen($this->request->json['body']) < 5) {
            $this->error('错误：评论正文字数太少。');
        }

        $nid = (int) $this->request->json['nid'];
        $n = new Node($nid, 'tid,status');
        if (!$n->exists() || $n->status == 0) {
            $this->error('话题不存在');
        }

        $user = new User($this->request->uid, 'createTime,status');
        try {
            // validate post for houston
            if (self::$_city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->json['body']);
            }

            $c = new Comment();
            $c->nid = $nid;
            $c->uid = $this->request->uid;
            $c->tid = $n->tid;
            $c->body = $this->request->json['body'];
            $c->createTime = $this->request->timestamp;
//            $c->hash = \crc32( $c->body );
            $c->add();
        } catch (\Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->_handleSpammer($user);
            }

            $this->logger->warn($e->getMessage() . \PHP_EOL . ' --comment-- ' . $this->request->json['body']);
            $this->error($e->getMessage());
        }

        // add files
        if (isset($this->request->json['files']) && sizeof($this->request->json['files']) > 0) {
            $image = new Image();
            $image->cityID = self::$_city->id;
            $image->addImages($this->request->json['files'], $this->config->path['file'], $nid, $c->id);
            $this->_getCacheEvent('ImageUpdate')->trigger();
        }

        $return = null;
        if (isset($this->request->json['returnCommentsAfter']) && \intval($this->request->json['returnCommentsAfter']) > 0) {
            $node = new Node();
            $comments = $node->getForumNodeCommentsAfter($nid, self::COMMENTS_PER_PAGE, \intval($this->request->json['returnCommentsAfter']));
            foreach ($comments as $i => $c) {
                $comments[$i]['city'] = $this->request->getCityFromIP($c['last_access_ip']);
                unset($comments[$i]['last_access_ip']);
            }
            $return['comments'] = $comments;
        }

        $this->_json($return);
    }

    /**
     * update a comment
     * uri: /api/comment/<nid>?action=put
     * post: key=<value>
     */
    public function put()
    {
        // edit existing comment
        if (!$this->request->uid || empty($this->args) || empty($this->request->json)) {
            $this->forbidden();
        }

        if (\strlen($this->request->json['body']) < 5) {
            $this->error('错误：评论正文字数太少。');
        }

        $cid = (int) $this->args[0];

        $c = new Comment($cid, 'nid,uid');
        if (!$c->exists()) {
            $this->error('评论不存在');
        }

        if ($this->request->uid != self::UID_ADMIN && $this->request->uid != $c->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }

        $n = new Node($c->nid, 'tid,status');
        if (!$n->exists() || $n->status == 0) {
            $this->error('话题不存在');
        }

        $user = new User($this->request->uid, 'createTime,status');
        try {
            // validate post for houston
            if (self::$_city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->json['body']);
            }

            $c->body = $this->request->json['body'];
            $c->lastModifiedTime = $this->request->timestamp;
//            $c->hash = \crc32( $c->body );
            $c->update('body,lastModifiedTime');
        } catch (\Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->_handleSpammer($user);
            }

            $this->logger->warn($e->getMessage() . \PHP_EOL . ' --comment-- ' . $this->request->json['body']);
            $this->error($e->getMessage());
        }

        // add files
        // update files
        $imageList = [];
        if (isset($this->request->json['files']) && sizeof($this->request->json['files']) > 0) {
            $image = new Image();
            $image->cityID = self::$_city->id;
            $imageList = $image->updateImages($this->request->json['files'], $this->config->path['file'], $c->nid, $cid);
            $this->_getCacheEvent('ImageUpdate')->trigger();
        }

        $this->_json(['files' => $imageList]);

        /*
          if ( $this->request->json['files'] )
          {
          $file = new Image();
          $file->cityID = self::$_city->id;
          $file->updateFileList( $this->request->json['files'], $this->config->path['file'], $nid, $c->id );
          $this->_getCacheEvent( 'ImageUpdate' )->trigger();
          } */

        // FORUM comments images
        /*
          if ( $this->request->json['update_file'] )
          {
          $files = \is_array( $this->request->json['files'] ) ? $this->request->json['files'] : [];
          $file = new Image();
          $file->cityID = self::$_city->id;
          $file->updateFileList( $files, $this->config->path['file'], $comment->nid, $cid );
          $this->_getIndependentCache( 'imageSlider' )->delete();
          } */

        // YP comments
        /*
          if ( isset( $this->request->json['star'] ) && \is_numeric( $this->request->json['star'] ) )
          {
          $rating = (int) $this->request->json['star'];
          if ( $rating > 0 )
          {
          $node = new Node();
          $node->updateRating( $comment->nid, $comment->uid, $rating, $this->request->timestamp );
          }
          } */
        /*
          $this->_getCacheEvent( 'NodeUpdate', $c->nid )->trigger();

          $this->_json( NULL ); */
    }

    /**
     * delete one comment or multiple modes
     * uri: /api/comment/<nid>(,<nid>,...)?action=delete
     */
    public function delete()
    {
        if (!$this->request->uid || empty($this->args)) {
            $this->forbidden();
        }

        $cid = (int) $this->args[0];

        $c = new Comment($cid, 'uid');
        if ($this->request->uid == $c->uid || $this->request->uid == self::UID_ADMIN) {
            $c->delete();
        } else {
            $this->forbidden();
        }

        $this->_json(null);
    }
}

//__END_OF_FILE__
