<?php

namespace site\controller\forum;

use site\controller\Forum;
use lzx\html\Template;
use site\dbobject\Tag;
use site\dbobject\Node;

class ForumCtrler extends Forum
{

   public function run()
   {
      $tag = $this->_getTagObj();
      $tagRoot = $tag->getTagRoot();
      $tagTree = $tag->getTagTree();
      
      $tid = $tag->id;
      $this->html->var[ 'head_title' ] = $tagTree[ $tid ][ 'name' ] . ' - ' . $this->html->var[ 'head_title' ];
      $this->html->var[ 'head_description' ] = $tagTree[ $tid ][ 'name' ] . ', ' . $this->html->var[ 'head_description' ];

      \sizeof( $tagTree[ $tid ][ 'children' ] ) ? $this->showForumList( $tid, $tagRoot, $tagTree ) : $this->showTopicList( $tid, $tagRoot );
   }

   // $forum, $groups, $boards are arrays of category id
   public function showForumList( $tid, $tagRoot, $tagTree )
   {
      $breadcrumb = [ ];
      foreach ( $tagRoot as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
            'title' => $t[ 'description' ],
            'name' => $t[ 'name' ]
         ];
      }

      $nodeInfo = [ ];
      $groupTrees = [ ];
      if ( $tid == Tag::FORUM_ID )
      {
         foreach ( $tagTree[ $tid ][ 'children' ] as $group_id )
         {
            $groupTrees[ $group_id ] = [ ];
            $group = $tagTree[ $group_id ];
            $groupTrees[ $group_id ][ $group_id ] = $group;
            foreach ( $group[ 'children' ] as $board_id )
            {
               $groupTrees[ $group_id ][ $board_id ] = $tagTree[ $board_id ];
               $nodeInfo[ $board_id ] = $this->_nodeInfo( $board_id );
               $this->cache->storeMap( '/forum/' . $board_id, '/forum/' . $group_id );
            }
         }
         $this->cache->storeMap( '/forum/' . $group_id, '/forum' );
      }
      else
      {
         $group_id = $tid;
         $groupTrees[ $group_id ] = [ ];
         $group = $tagTree[ $group_id ];
         $groupTrees[ $group_id ][ $group_id ] = $group;
         foreach ( $group[ 'children' ] as $board_id )
         {
            $groupTrees[ $group_id ][ $board_id ] = $tagTree[ $board_id ];
            $nodeInfo[ $board_id ] = $this->_nodeInfo( $board_id );
            $this->cache->storeMap( '/forum/' . $board_id, '/forum/' . $group_id );
         }
      }
      $contents = ['groups' => $groupTrees, 'nodeInfo' => $nodeInfo ];
      if ( \sizeof( $breadcrumb ) > 1 )
      {
         $contents[ 'breadcrumb' ] = $this->html->breadcrumb( $breadcrumb );
      }
      $this->html->var[ 'content' ] = new Template( 'forum_list', $contents );
   }

   public function showTopicList( $tid, $tagRoot )
   {

      $breadcrumb = [ ];
      foreach ( $tagRoot as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
            'title' => $t[ 'description' ],
            'name' => $t[ 'name' ]
         ];
      }

      $node = new Node();
      list($pageNo, $pageCount) = $this->_getPagerInfo( $node->getNodeCount( $tid ), self::NODES_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/forum/' . $tid );

      $nodes = $node->getForumNodeList( $tid, self::NODES_PER_PAGE, ($pageNo - 1) * self::NODES_PER_PAGE );

      $nids = [ ];
      foreach ( $nodes as $i => $n )
      {
         $nids[] = $n[ 'id' ];
         $nodes[ $i ][ 'title' ] = $this->html->truncate( $n[ 'title' ], 45 );
         $nodes[ $i ][ 'create_time' ] = \date( 'm/d/Y H:i', $n[ 'create_time' ] );
         $nodes[ $i ][ 'comment_time' ] = \date( 'm/d/Y H:i', $n[ 'comment_time' ] );
      }

      $editor_contents = [
         'display' => FALSE,
         'title_display' => TRUE,
         'node_title' => '',
         'form_handler' => '/forum/' . $tid . '/node',
      ];
      $editor = new Template( 'editor_bbcode', $editor_contents );

      // will not build node-forum map, would be too many nodes point to forum, too big map

      $contents = [
         'tid' => $tid,
         'boardDescription' => $tagRoot[ $tid ][ 'description' ],
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'nodes' => (empty( $nodes ) ? NULL : $nodes),
         'editor' => $editor,
         'ajaxURI' => '/forum/ajax/viewcount?type=json&tid=' . $tid . '&nids=' . \implode( '_', $nids ) . '&nosession',
      ];
      $this->html->var[ 'content' ] = new Template( 'topic_list', $contents );
   }

   protected function _nodeInfo( $tid )
   {
      $tag = new Tag( $tid, NULL );
      $nodeInfo = $tag->getNodeInfo();

      $nodeInfo[ 'title' ] = $this->html->truncate( $nodeInfo[ 'title' ], 35 );
      $nodeInfo[ 'create_time' ] = \date( 'm/d/Y H:i', $nodeInfo[ 'create_time' ] );
      return $nodeInfo;
   }

}

//__END_OF_FILE__
