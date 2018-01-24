<?php declare(strict_types=1);

namespace site\handler\api\viewcount;

use lzx\exception\ErrorMessage;
use lzx\exception\Forbidden;
use site\Service;
use site\dbobject\Node;

class Handler extends Service
{
    public function get(): void
    {
        if (!$this->args) {
            throw new Forbidden();
        }

        $viewCount = [];
        $nids = array_filter(array_map('intval', explode(',', $this->args[0])), function (int $v): bool {
            return $v > 0;
        });

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
                    $node->viewCount += 1;
                    $node->update('viewCount');
                    $viewCount['viewCount' . $node->id] = $node->viewCount;
                } else {
                    throw new ErrorMessage('node not exist: ' . $node->id);
                }
            }
        } else {
            throw new ErrorMessage('invalid node ids: ' . $this->args[0]);
        }

        $this->json($viewCount);
    }
}
