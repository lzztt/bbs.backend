<?php

namespace site\handler\api\bookmark;

use site\Service;
use site\dbobject\User;

class Handler extends Service
{
    const NODES_PER_PAGE = 20;
    /**
     * get bookmarks for a user
     * uri: /api/bookmark/<uid>
     *        /api/bookmark/<uid>?p=<pageNo>
     */
    public function get()
    {
        if (!$this->request->uid || empty($this->args) || !is_numeric($this->args[0])) {
            $this->forbidden();
        }

        $uid = (int) $this->args[0];

        if ($uid != $this->request->uid) {
            $this->forbidden();
        }

        $u = new User($this->request->uid, null);

        $nodeCount = $u->countBookmark();
        list($pageNo, $pageCount) = $this->getPagerInfo($nodeCount, self::NODES_PER_PAGE);

        $nodes = $nodeCount > 0 ? $u->listBookmark(self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE) : [];

        $this->json(['nodes' => $nodes, 'pager' => ['pageNo' => $pageNo, 'pageCount' => $pageCount]]);
    }

    /**
     * add a node to user's bookmark list
     * uri: /api/bookmark[?action=post]
     * post: nid=<nid>
     */
    public function post()
    {
        if (!$this->request->uid || empty($this->request->post)) {
            $this->forbidden();
        }

        $nid = (int) $this->request->post['nid'];
        if ($nid <= 0) {
            $this->error('node does not exist');
        }

        $u = new User($this->request->uid, null);

        $u->addBookmark($nid);

        $this->json(null);
    }

    /**
     * remove one node or multiple modes from user's bookmark list
     * uri: /api/bookmark/<nid>(,<nid>,...)?action=delete
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

        $u = new User($this->request->uid, null);
        foreach ($nids as $nid) {
            $u->deleteBookmark($nid);
        }

        $this->json(null);
    }
}
