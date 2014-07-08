<?php

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;
use site\dbobject\NodeYellowPage;
use site\dbobject\Image;

abstract class YP extends Controller
{

   const YP_ROOT_TID = 2;
   const NODES_PER_PAGE = 25;

   public function run()
   {
      

      if ( is_null( $this->args[1] ) )
      {
         $this->showYellowPageHome();
      }
      elseif ( is_numeric( $this->args[1] ) )
      {
         $tid = (int) $this->args[1];
         if ( $this->args[2] == 'node' )
         {
            $this->createYellowPageNode( $tid );
         }
         else
         {
            $this->showYellowPageList( $tid );
         }
      }
      else
      {
         if ( $this->args[1] == 'join' )
         {
            $this->showYellowPageJoin();
         }
         else
         {
            $this->request->pageNotFound();
         }
      }
   }

   public function showYellowPageJoin()
   {
      $this->html->var['content'] = new Template( 'yp_join' );
   }

// $yp, $groups, $boards are arrays of category id
   public function showYellowPageHome()
   {
      $tag = new Tag();
      $tag->id = self::YP_ROOT_TID;
      $yp = $tag->getTagTree();
      $this->html->var['content'] = new Template( 'yp_home', ['tid' => $tag->id, 'yp' => $yp] );
   }

   public function showYellowPageList( $tid )
   {
      $tag = new Tag( $tid, NULL );
      $tagRoot = $tag->getTagRoot();
      $tids = \implode( ',', $tag->getLeafTIDs() );

      $breadcrumb = [];
      foreach ( $tagRoot as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::YP_ID ? '/yp' : ('/yp/' . $i)),
            'title' => $t['description'],
            'name' => $t['name']
         ];
      }

      $node = new Node();
      $nodeCount = $node->getNodeCount( $tids );
      list($pageNo, $pageCount) = $this->_getPagerInfo( $node->getNodeCount( $tid ), self::NODES_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/yp/' . $tid );

      $nodes = $node->getYellowPageNodeList( $tids, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE );

      $nids = [];
      foreach ( $nodes as $i => $n )
      {
         $nids[] = $n['id'];
         $nodes[$i]['title'] = $this->html->truncate( $n['title'], 45 );
      }

      $contents = [
         'tid' => $tid,
         'cateName' => $tag->name,
         'cateDescription' => $tag->description,
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'nodes' => (empty( $nodes ) ? NULL : $nodes),
         'ajaxURI' => '/yp/ajax/viewcount?type=json&tid=' . $tid . '&nids=' . \implode( '_', $nids ) . '&nosession',
      ];
      $this->html->var['content'] = new Template( 'yp_list', $contents );
   }

   protected function ajax()
   {
      // url = /forum/ajax/viewcount?tid=<tid>&nids=<nid>_<nid>_

      $viewCount = [];
      if ( $this->args[2] == 'viewcount' && \strlen( $this->request->get['nids'] ) > 0 )
      {
         //$tid = \intval($this->request->get['tid']);
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
            foreach ( $node->getViewCounts( $nids ) as $r )
            {
               $viewCount['viewCount_' . $r['id']] = (int) $r['view_count'];
            }
         }
      }

      return $viewCount;
   }

   public function createYellowPageNode( $tid )
   {
      if ( $this->request->uid != 1 )
      {
         $this->request->pageForbidden();
      }

      $tag = new Tag();
      $tag->parent = $tid;
      if ( $tag->getCount() > 0 )
      {
         $this->error( '错误：您不能在该类别中添加黄页，请到它的子类别中添加。' );
      }

      if ( empty( $this->request->post ) )
      {
         $this->html->var['content'] = new Template( 'editor_bbcode_yp' );
      }
      else
      {
         $node = new Node();
         $node->tid = $tid;
         $node->uid = $this->request->uid;
         $node->title = $this->request->post['title'];
         $node->body = $this->request->post['body'];
         $node->createTime = $this->request->timestamp;
         $node->status = 1;
         $node->add();

         $nodeYP = new NodeYellowPage();
         $nodeYP->nid = $node->id;
         foreach ( \array_diff( $nodeYP->getProperties(), ['nid'] ) as $key )
         {
            $nodeYP->$key = $this->request->post[$key];
         }
         $nodeYP->add();

         if ( isset( $this->request->post['files'] ) )
         {
            $file = new Image();
            $file->updateFileList( $this->request->post['files'], $this->config->path['file'], $node->id );
         }

         $this->cache->delete( '/yp/' . $tid );
         $this->cache->delete( 'latestYellowPages' );

         $this->request->redirect( '/node/' . $node->id );
      }
   }

}

//__END_OF_FILE__