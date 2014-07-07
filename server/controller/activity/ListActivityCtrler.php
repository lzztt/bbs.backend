<?php

namespace site\controller\activity;

use site\controller\Activity;
use lzx\html\Template;
use site\dbobject\Activity as ActivityObject;

class ListActivityCtrler extends Activity
{

    const NODES_PER_PAGE = 25;

    public function run()
    {
        
        $this->listActivity();
    }

    public function listActivity()
    {
        $act = new ActivityObject();
        $act->status = 1;
        $total = $act->getCount();

        if ( $total == 0 )
        {
            $this->error( '目前没有活动。' );
            return;
        }

        $pageNo = $this->request->get['page'] ? \intval( $this->request->get['page'] ) : 1;
        $pageCount = \ceil( $total / self::NODES_PER_PAGE );

        if ( $pageNo < 1 || $pageNo > $pageCount )
        {
            $pageNo = $pageCount;
        }
        $pager = $this->html->pager( $pageNo, $pageCount, '/activity' );

        $limit = self::NODES_PER_PAGE;
        $offset = ($pageNo - 1) * self::NODES_PER_PAGE;
        $actList = $act->getActivityList( $limit, $offset );

        foreach ( $actList as $k => $n )
        {
            $type = ($n['startTime'] < $this->request->timestamp) ? (($n['endTime'] > $this->request->timestamp) ? 'activity_now' : 'activity_before') : 'activity_future';
            $data .= '<li class="' . (($k % 2 == 0) ? 'even' : 'odd') . '"><a href="/node/' . $n['nid'] . '"><span class="' . $type . '">[' . date( 'm/d', $n['startTime'] ) . ']</span> ' . $this->html->truncate( $n['title'], 80 ) . '</a></li>';
        }

        $contents = [
            'pager' => $pager,
            'data' => $data
        ];

        $this->html->var['content'] = new Template( 'activity_list', $contents );
    }

}

//__END_OF_FILE__