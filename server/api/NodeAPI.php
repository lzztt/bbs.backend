<?php

namespace site\api;

use site\Service;
use site\dbobject\Node;
use site\dbobject\User;
use site\dbobject\Image;
use lzx\core\BBCodeMarkx;

class NodeAPI extends Service
{
    const COMMENTS_PER_PAGE = 20;

    /**
     * get nodes for a user
     * uri: /api/node/<nid>
     *        /api/node/<nid>?p=<pageNo>
     */
    public function get()
    {
        if (empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $nid = (int) $this->args[0];

        $data = [];

        $nodeObj = new Node();
        $node = $nodeObj->getForumNode($nid, true);

        if (!$node) {
            $this->error('page not found');
        }

        $data['tags'] = array_values($nodeObj->getTags($nid));

        // get page info
        list($pageNo, $pageCount) = $this->getPagerInfo($node['comment_count'], self::COMMENTS_PER_PAGE);

        if ($pageNo == 1) {
            $data['topic'] = $node;
        } else {
            $data['topic'] = [
                'title'            => $node['title'],
                'view_count'     => $node['view_count'],
                'comment_count' => $node['comment_count']
            ];
        }

        $data['pager'] = [
            'pageNo'             => $pageNo,
            'pageCount'         => $pageCount,
            'commentsPerPage' => self::COMMENTS_PER_PAGE
        ];

        $data['comments'] = [];
        if ($node['comment_count'] > 0) {
            $comments = $nodeObj->getForumNodeComments($nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE, true);
            foreach ($comments as $i => $c) {
                $comments[$i]['city'] = $this->request->getCityFromIP($c['last_access_ip']);
                unset($comments[$i]['last_access_ip']);

                if (strpos($c['body'], '[/') !== false) {
                    $comments[$i]['body'] = BBCodeMarkx::parse($c['body']);
                }
            }
            $data['comments'] = $comments;
        }

        $this->json($data);
    }

    /**
     * add a new node
     * uri: /api/node[?action=post]
     * post: key=<value>
     */
    public function post()
    {
        if (!$this->request->uid || empty($this->request->json)) {
            $this->forbidden();
        }

        if (strlen($this->request->json['body']) < 5 || strlen($this->request->json['title']) < 5) {
            $this->error('错误：标题或正文字数太少。');
        }

        $user = new User($this->request->uid, 'createTime,status');
        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->json['body'], $this->request->json['title']);
            }

            $node = new Node();
            $node->tid = $tid;
            $node->uid = $this->request->uid;
            $node->title = $this->request->json['title'];
            $node->body = $this->request->json['body'];
            $node->createTime = $this->request->timestamp;
            $node->status = 1;
            $node->add();
        } catch (\Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->handleSpammer($user);
            }

            $this->logger->error($e->getMessage() . \PHP_EOL . ' --node-- ' . $this->request->json['title'] . PHP_EOL . $this->request->json['body']);
            $this->error($e->getMessage());
        }

        // add files
        if (isset($this->request->json['files']) && sizeof($this->request->json['files']) > 0) {
            $image = new Image();
            $image->cityID = self::$city->id;
            $image->addImages($this->request->json['files'], $this->config->path['file'], $node->id);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->json(['nid' => $node->id]);
    }

    /**
     * update a node
     * uri: /api/node/<nid>?action=put
     * post: key=<value>
     */
    public function put()
    {
        if (!$this->request->uid || empty($this->args) || empty($this->request->json)) {
            $this->forbidden();
        }

        if (isset($this->request->json['title']) && strlen($this->request->json['title']) < 5) {
            $this->error('错误：标题字数太少。');
        }

        if (isset($this->request->json['body']) && strlen($this->request->json['body']) < 5) {
            $this->error('错误：正文字数太少。');
        }

        $nid = (int) $this->args[0];

        $n = new Node($nid, 'uid,status');
        if (!$n->exists() || $n->status == 0) {
            $this->error('话题不存在');
        }

        if ($this->request->uid != self::UID_ADMIN && $this->request->uid != $n->uid) {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }

        $user = new User($this->request->uid, 'createTime,status');
        try {
            // validate post for houston
            if (self::$city->id == 1) {
                $user->validatePost($this->request->ip, $this->request->timestamp, $this->request->json['body'], $this->request->json['title']);
            }

            if (isset($this->request->json['body']) || isset($this->request->json['title'])) {
                // update node content
                $n->title = $this->request->json['title'];
                $n->body = $this->request->json['body'];
                $n->lastModifiedTime = $this->request->timestamp;
            } elseif (isset($this->request->json['tid'])) {
                // update tag
                $n->tid = $this->request->json['tid'];
            } else {
                $this->error('Unsupported node update');
            }
            $n->update();
        } catch (\Exception $e) {
            // spammer found
            if ($user->isSpammer()) {
                $this->handleSpammer($user);
            }

            $this->logger->error($e->getMessage() . \PHP_EOL . ' --node-- ' . $this->request->json['title'] . PHP_EOL . $this->request->json['body']);
            $this->error($e->getMessage());
        }

        // update files
        $imageList = [];
        if (isset($this->request->json['files']) && sizeof($this->request->json['files']) > 0) {
            $image = new Image();
            $image->cityID = self::$city->id;
            $imageList = $image->updateImages($this->request->json['files'], $this->config->path['file'], $nid);
            $this->getCacheEvent('ImageUpdate')->trigger();
        }

        $this->json(['files' => $imageList]);
    }

    /**
     * delete one node or multiple modes
     * uri: /api/node/<nid>(,<nid>,...)?action=delete
     */
    public function delete()
    {
        if (!$this->request->uid || empty($this->args)) {
            $this->forbidden();
        }

        $nids = [];

        foreach (explode(',', $this->args[0]) as $nid) {
            if (is_numeric($nid) && intval($nid) > 0) {
                $nids[] = (int) $nid;
            }
        }

        $n = new Node();
        foreach ($nids as $nid) {
            $n->id = $nid;
            $n->status = 0;
            $n->update();
        }

        $this->json(null);
    }
}

//__END_OF_FILE__
