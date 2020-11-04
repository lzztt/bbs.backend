<?php

declare(strict_types=1);

namespace site\handler\yp;

use lzx\exception\NotFound;
use lzx\html\HtmlElement;
use site\Controller;
use site\dbobject\Node;
use site\dbobject\Tag;
use site\gen\theme\roselife\YpHome;
use site\gen\theme\roselife\YpList;

class Handler extends Controller
{
    const NODES_PER_PAGE = 25;

    public function run(): void
    {
        $this->cache = $this->getPageCache();

        $tid = $this->args ? (int) $this->args[0] : 0;
        if ($tid <= 0) {
            $this->ypHome();
        } else {
            $this->nodeList($tid);
        }
    }

    protected function ypHome(): void
    {
        throw new NotFound();
    }

    protected function nodeList(int $tid): void
    {
        $tag = new Tag($tid, 'id');
        $tids = implode(',', $tag->getLeafTIDs());

        $node = new Node();
        list($pageNo, $pageCount) = $this->getPagerInfo($node->getNodeCount($tids), self::NODES_PER_PAGE);
        $pager = HtmlElement::pager($pageNo, $pageCount, '/yp/' . $tid);

        $nodes = $node->getYellowPageNodeList($tids, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE);

        $nids = array_column($nodes, 'id');

        $this->html->setContent(
            (new YpList())
                ->setPager($pager)
                ->setNodes($nodes ? $nodes : [])
                ->setAjaxUri('/api/viewcount/' . implode(',', $nids))
        );
    }
}
