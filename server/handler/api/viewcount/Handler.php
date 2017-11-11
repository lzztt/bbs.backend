<?php

namespace site\handler\api\viewcount;

use site\Service;
use site\dbobject\Node;

class Handler extends Service
{
    public function get()
    {
        if (!$this->args) {
            $this->forbidden();
        }

        $viewCount = [];
        $nids = [];

        foreach (explode(',', $this->args[0]) as $nid) {
            if (is_numeric($nid) && intval($nid) > 0) {
                $nids[] = (int) $nid;
            }
        }
        if ($nids) {
            $node = new Node();
            if (sizeof($nids) > 1) {
                // multiple nodes
                foreach ($node->getViewCounts($nids) as $r) {
                    $viewCount['viewCount' . $r['id']] = (int) $r['view_count'];
                }
            } else {
                // single node: update view count
                $node->id = $nids[0];
                $node->load('viewCount');
                if ($node->exists()) {
                    $node->viewCount = $node->viewCount + 1;
                    $node->update('viewCount');
                    $viewCount['viewCount' . $node->id] = $node->viewCount;
                } else {
                    $this->error('node not exist: ' . $node->id);
                }
            }
        } else {
            $this->error('invalid node ids: ' . $this->args[0]);
        }

        $this->json($viewCount);
    }
}

//__END_OF_FILE__
