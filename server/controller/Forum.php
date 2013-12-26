<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\dbobject\Image;
use site\dbobject\User;

class Forum extends Controller
{

    const NODES_PER_PAGE = 25;

    public function run()
    {
        parent::run();
        $this->checkAJAX();

        if ( $this->request->args[1] == 'help' )
        {
            $this->showForumHelp();
            return;
        }

        $tag = new Tag();
        if ( \is_numeric( $this->request->args[1] ) )
        {
            $tid = \intval( $this->request->args[1] );
            $tag->id = $tid;
            $tag->load( 'id' );
            if ( $tag->exists() === FALSE )
            {
                $this->request->pageNotFound();
            }
            $tagRoot = $tag->getTagRoot();
            if ( !\array_key_exists( Tag::FORUM_ID, $tagRoot ) )
            {
                $this->request->pageNotFound();
            }
        }
        else
        {
            $tid = Tag::FORUM_ID;
            $tag->id = $tid;
            $tagRoot = $tag->getTagRoot();
        }

        $tagTree = $tag->getTagTree();

        if ( $this->request->args[2] == 'node' )
        {
            \sizeof( $tagTree[$tid]['children'] ) ? $this->error( 'Could not post topic in this forum' ) : $this->createForumTopic( $tid );
        }
        else
        {
            $this->html->var['head_title'] = $tagTree[$tid]['name'] . ' - ' . $this->html->var['head_title'];
            $this->html->var['head_description'] = $tagTree[$tid]['name'] . ', ' . $this->html->var['head_description'];

            \sizeof( $tagTree[$tid]['children'] ) ? $this->showForum( $tid, $tagRoot, $tagTree ) : $this->showForumTopic( $tid, $tagRoot );
        }
    }

    public function ajax()
    {
        // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

        $viewCount = array();
        if ( $this->request->args[2] == 'viewcount' && \strlen( $this->request->get['nids'] ) > 0 )
        {
            $tid = \intval( $this->request->get['tid'] );
            $nids = \explode( '_', $this->request->get['nids'] );
            foreach ( $nids as $i => $nid )
            {
                if ( \strlen( $nid ) > 0 )
                {
                    $nids[$i] = \intval( $nid );
                }
                else
                {
                    unset( $nids[$i] );
                }
            }
            if ( \sizeof( $nids ) > 0 )
            {
                $node = new Node();
                //$node->tid = $tid;
                $node->where( 'id', $nids, '=' );
                $arr = $node->getList( 'id,viewCount' );

                foreach ( $arr as $r )
                {
                    $viewCount['viewCount_' . $r['id']] = (int) $r['viewCount'];
                }
            }
        }

        return $viewCount;
    }

    public function showForumHelp()
    {
        $this->request->redirect( '/help' );
    }

    public function nodeInfo( $tid )
    {
        $tag = new Tag( $tid, NULL );
        $nodeInfo = $tag->getNodeInfo();

        $nodeInfo['title'] = $this->html->truncate( $nodeInfo['title'], 35 );
        $nodeInfo['create_time'] = \date( 'm/d/Y H:i', $nodeInfo['create_time'] );
        return $nodeInfo;
    }

// $forum, $groups, $boards are arrays of category id
    public function showForum( $tid, $tagRoot, $tagTree )
    {
        $breadcrumb = [];
        foreach ( $tagRoot as $i => $t )
        {
            $breadcrumb[] = [
                'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
                'title' => $t['description'],
                'name' => $t['name']
            ];
        }

        $nodeInfo = [];
        $groupTrees = [];
        if ( $tid == Tag::FORUM_ID )
        {
            foreach ( $tagTree[$tid]['children'] as $group_id )
            {
                $groupTrees[$group_id] = [];
                $group = $tagTree[$group_id];
                $groupTrees[$group_id][$group_id] = $group;
                foreach ( $group['children'] as $board_id )
                {
                    $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                    $nodeInfo[$board_id] = $this->nodeInfo( $board_id );
                    $this->cache->storeMap( '/forum/' . $board_id, '/forum/' . $group_id );
                }
            }
            $this->cache->storeMap( '/forum/' . $group_id, '/forum' );
        }
        else
        {
            $group_id = $tid;
            $groupTrees[$group_id] = [];
            $group = $tagTree[$group_id];
            $groupTrees[$group_id][$group_id] = $group;
            foreach ( $group['children'] as $board_id )
            {
                $groupTrees[$group_id][$board_id] = $tagTree[$board_id];
                $nodeInfo[$board_id] = $this->nodeInfo( $board_id );
                $this->cache->storeMap( '/forum/' . $board_id, '/forum/' . $group_id );
            }
        }
        $this->html->var['content'] = new Template( 'forum_list', array('breadcrumb' => $breadcrumb, 'groups' => $groupTrees, 'nodeInfo' => $nodeInfo) );
    }

    public function showForumTopic( $tid, $tagRoot )
    {
        $node = new Node();
        $nodeCount = $node->getNodeCount( $tid );

        $breadcrumb = [];
        foreach ( $tagRoot as $i => $t )
        {
            $breadcrumb[] = [
                'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
                'title' => $t['description'],
                'name' => $t['name']
            ];
        }

        $pageNo = $this->request->get['page'];
        $pageCount = \ceil( $nodeCount / self::NODES_PER_PAGE );
        list($pageNo, $pager) = $this->html->generatePager( $pageNo, $pageCount, '/forum/' . $tid );

        $node = new Node();
        $nodes = $node->getForumNodeList( $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE );

        $nids = [];
        foreach ( $nodes as $i => $n )
        {
            $nids[] = $n['id'];
            $nodes[$i]['title'] = $this->html->truncate( $n['title'], 45 );
            $nodes[$i]['create_time'] = \date( 'm/d/Y H:i', $n['create_time'] );
            $nodes[$i]['comment_time'] = \date( 'm/d/Y H:i', $n['comment_time'] );
        }

        $editor_contents = array(
            'display' => FALSE,
            'title_display' => TRUE,
            'node_title' => '',
            'form_handler' => '/forum/' . $tid . '/node',
        );
        $editor = new Template( 'editor_bbcode', $editor_contents );

        // will not build node-forum map, would be too many nodes point to forum, too big map

        $contents = array(
            'tid' => $tid,
            'boardName' => $tagRoot[$tid]['name'],
            'boardDescription' => $tagRoot[$tid]['description'],
            'breadcrumb' => $breadcrumb,
            'pager' => $pager,
            'nodes' => (empty( $nodes ) ? NULL : $nodes),
            'editor' => $editor,
            'ajaxURI' => '/forum/ajax/viewcount?type=json&tid=' . $tid . '&nids=' . \implode( '_', $nids ),
        );
        $this->html->var['content'] = new Template( 'topic_list', $contents );
    }

    public function createForumTopic( $tid )
    {
        if ( $this->request->uid <= 0 )
        {
            $this->error( 'Please login first' );
        }

        if ( \strlen( $this->request->post['body'] ) < 5 || \strlen( $this->request->post['title'] ) < 5 )
        {
            $this->error( 'Topic title or body is too short.' );
        }

        $node = new Node();
        $node->tid = $tid;
        $node->uid = $this->request->uid;
        $node->title = $this->request->post['title'];
        $node->body = $this->request->post['body'];
        $node->createTime = $this->request->timestamp;
        $node->status = 1;
        try
        {
            $node->add();
        }
        catch ( \Exception $e )
        {
            $this->logger->error( ' --node-- ' . $node->title . PHP_EOL . $node->body );
            $this->error( $e->getMessage(), TRUE );
        }


        if ( isset( $this->request->post['files'] ) )
        {
            $file = new Image();
            $file->updateFileList( $this->request->post['files'], $this->path['file'], $node->nid );
            $this->cache->delete( 'imageSlider' );
        }

        $user = new User( $node->uid, 'points' );
        $user->points += 3;
        $user->update( 'points' );

        $this->cache->delete( '/forum/' . $tid );
        $this->cache->delete( 'latestForumTopics' );
        if ( $node->tid == 15 )
        {
            $this->cache->delete( 'latestImmigrationPosts' );
        }

        $this->request->redirect( '/node/' . $node->nid );
    }

}

//__END_OF_FILE__
