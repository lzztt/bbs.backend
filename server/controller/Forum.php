<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use site\dataobject\Tag;
use site\dataobject\Node;
use site\dataobject\Image;
use site\dataobject\User;

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

      if ( is_numeric( $this->request->args[1] ) )
      {
         $tid = (int) $this->request->args[1];
         $tag = new Tag( $tid );
         if ( $tag->root != 1 )
         {
            $this->request->pageNotFound();
         }
      }
      else
      {
         $tid = 1;
      }

      $tag = new Tag();
      $tag->tid = $tid;
      $forum = $tag->getTagTree();

      if ( empty( $forum ) )
      {
         $this->request->pageNotFound();
      }
      else
      {
         if ( $this->request->args[2] == 'node' )
         {
            isset( $forum['children'] ) ? $this->error( 'Could not post topic in this forum' ) : $this->createForumTopic( $forum );
         }
         else
         {
            $this->html->var['head_title'] = $forum['name'] . ' - ' . $this->html->var['head_title'];
            $this->html->var['head_description'] = $forum['name'] . ', ' . $this->html->var['head_description'];

            isset( $forum['children'] ) ? $this->showForum( $forum ) : $this->showForumTopic( $forum );
         }
      }
   }

   public function ajax()
   {
      // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

      $viewCount = array( );
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
            $node->where( 'nid', $nids, '=' );
            $arr = $node->getList( 'nid,viewCount' );

            foreach ( $arr as $r )
            {
               $viewCount['viewCount_' . $r['nid']] = (int) $r['viewCount'];
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
      $tag = new Tag();
      $tag->tid = $tid;
      $nodeInfo = $tag->getNodeInfo();

      $nodeInfo['title'] = $this->html->truncate( $nodeInfo['title'], 35 );
      $nodeInfo['createTime'] = date( 'm/d/Y H:i', $nodeInfo['createTime'] );
      return $nodeInfo;
   }

// $forum, $groups, $boards are arrays of category id
   public function showForum( $forum )
   {
      $nids = array( );

      foreach ( $forum['children'] as $i => $child )
      {
         if ( \array_key_exists( 'children', $child ) )
         {
            foreach ( $child['children'] as $j => $grandchild )
            {
               $nodeInfo = $this->nodeInfo( $grandchild['tid'] );
               $forum['children'][$i]['children'][$j]['nodeInfo'] = $nodeInfo;
               $this->cache->storeMap( '/forum/' . $grandchild['tid'], '/forum/' . $child['tid'] );
            }
         }
         else
         {
            $nodeInfo = $this->nodeInfo( $child['tid'] );
            $forum['children'][$i]['nodeInfo'] = $nodeInfo;
         }

         $this->cache->storeMap( '/forum/' . $child['tid'], '/forum' );

         $nids[] = $nodeInfo['nid'];
      }

      // build node forum cache map
      foreach ( $nids as $nid )
      {
         $this->cache->storeMap( '/node/' . $nid, '/forum/' . $forum['tid'] );
      }

      if ( $forum['tid'] != 1 ) // subgroup
      {
         $forum['children'] = array( $forum ); // as root forum
      }

      $this->html->var['content'] = new Template( 'forum_list', array( 'forum' => $forum ) );
   }

   public function showForumTopic( $forum )
   {

      $node = new Node();
      $nodeCount = $node->getNodeCount( $forum['tid'] );

      $tag = new Tag();
      $tag->tid = $forum['tid'];
      $parent = $tag->getParent( 'tid,name' );
      $breadcrumb = '<a href="/forum">Forum</a> > <a href="/forum/' . $parent['tid'] . '">' . $parent['name'] . '</a>';

      $pageNo = $this->request->get['page'];
      $pageCount = ceil( $nodeCount / self::NODES_PER_PAGE );
      list($pageNo, $pager) = $this->html->generatePager( $pageNo, $pageCount, '/forum/' . $forum['tid'] );


      $node = new Node();
      $nodes = $node->getForumNodeList( $forum['tid'], self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE );

      $nids = array( );
      foreach ( $nodes as $i => $n )
      {
         $nids[] = $n['nid'];
         $nodes[$i]['title'] = $this->html->truncate( $n['title'], 45 );
         $nodes[$i]['createTime'] = date( 'm/d/Y H:i', $n['createTime'] );
         $nodes[$i]['lastCommentTime'] = date( 'm/d/Y H:i', $n['lastCommentTime'] );
      }

      $editor_contents = array(
         'display' => FALSE,
         'title_display' => TRUE,
         'node_title' => '',
         'form_handler' => '/forum/' . $forum['tid'] . '/node',
      );
      $editor = new Template( 'editor_bbcode', $editor_contents );

      // build node forum cache map
      /*
       * would be too many nodes point to forum, too big map
        foreach ($nids as $nid)
        {
        $this->cache->storeMap('/node/' . $nid, '/forum/' . $forum['tid']);
        }
       */

      $contents = array(
         'tid' => $forum['tid'],
         'boardName' => $board['name'],
         'boardDescription' => $board['description'],
         'breadcrumb' => $breadcrumb,
         'pager' => $pager,
         'nodes' => (empty( $nodes ) ? NULL : $nodes),
         'editor' => $editor,
         'ajaxURI' => '/forum/ajax/viewcount?type=json&tid=' . $forum['tid'] . '&nids=' . \implode( '_', $nids ),
      );
      $this->html->var['content'] = new Template( 'topic_list', $contents );
   }

   public function createForumTopic( $forum )
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
      $node->tid = $forum['tid'];
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

      $this->cache->delete( '/forum/' . $forum['tid'] );
      $this->cache->delete( 'latestForumTopics' );
      if ( $node->tid == 15 )
      {
         $this->cache->delete( 'latestImmigrationPosts' );
      }

      $this->request->redirect( '/node/' . $node->nid );
   }

}

//__END_OF_FILE__
