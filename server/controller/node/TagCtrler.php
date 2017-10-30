<?php

namespace site\controller\node;

use site\controller\Node;
use site\dbobject\Node as NodeObject;

class TagCtrler extends Node
{
    public function run()
    {
        list($nid, $type) = $this->getNodeType();
        $method = 'tag' . $type;
        $this->$method($nid);
    }

    private function tagForumTopic($nid)
    {
        if (empty($this->args)) {
            $this->error('no tag id specified');
        }

        $newTagID = (int) $this->args[0];

        $nodeObj = new NodeObject($nid, 'uid,tid');
        if ($this->request->uid == 1 || $this->request->uid == $nodeObj->uid) {
            $oldTagID = $nodeObj->tid;
            $nodeObj->tid = $newTagID;
            $nodeObj->update('tid');

            foreach (['/forum/' . $oldTagID, '/forum/' . $newTagID, '/node/' . $nid] as $key) {
                $this->getIndependentCache($key)->delete();
            }

            $this->pageRedirect('/node/' . $nid);
        } else {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }
    }

    private function tagYellowPage($nid)
    {
        if (empty($this->args)) {
            $this->error('no tag id specified');
        }

        $newTagID = (int) $this->args[0];

        $nodeObj = new NodeObject($nid, 'uid,tid');
        if ($this->request->uid == 1 || $this->request->uid == $nodeObj->uid) {
            $oldTagID = $nodeObj->tid;
            $nodeObj->tid = $newTagID;
            $nodeObj->update('tid');

            foreach (['/yp/' . $oldTagID, '/yp/' . $newTagID, '/node/' . $nid] as $key) {
                $this->getIndependentCache($key)->delete();
            }

            $this->pageRedirect('/node/' . $nid);
        } else {
            $this->logger->warn('wrong action : uid = ' . $this->request->uid);
            $this->pageForbidden();
        }
    }
}

//__END_OF_FILE__
