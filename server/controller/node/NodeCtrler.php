<?php

namespace site\controller\node;

use site\controller\Node;
use lzx\core\BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use site\dbobject\Node as NodeObject;
use lzx\cache\PageCache;

class NodeCtrler extends Node
{

   public function run()
   {
      $this->cache = new PageCache( $this->request->uri );

      list($nid, $type) = $this->_getNodeType();
      $method = '_display' . $type;
      $this->$method( $nid );

      $this->_getCacheEvent( 'NodeUpdate', $nid )->addListener( $this->cache );
   }

   private function _displayForumTopic( $nid )
   {
      $nodeObj = new NodeObject();
      $node = $nodeObj->getForumNode( $nid );
      $tags = $nodeObj->getTags( $nid );

      $this->html->var[ 'head_title' ] = $node[ 'title' ] . ' - ' . $this->html->var[ 'head_title' ];
      $this->html->var[ 'head_description' ] = $node[ 'title' ] . ', ' . $this->html->var[ 'head_description' ];

      if ( !$node )
      {
         $this->pageNotFound();
      }

      $breadcrumb = [ ];
      foreach ( $tags as $i => $t )
      {
         $breadcrumb[ $t[ 'name' ] ] = ($i === self::$_city->ForumRootID ? '/forum' : ('/forum/' . $i));
      }
      $breadcrumb[ $node[ 'title' ] ] = NULL;

      list($pageNo, $pageCount) = $this->_getPagerInfo( $node[ 'comment_count' ], self::COMMENTS_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/node/' . $node[ 'id' ] );

      $postNumStart = ($pageNo > 1) ? ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1 : 0; // first page start from the node and followed by comments

      $contents = [
         'nid' => $nid,
         'tid' => $node[ 'tid' ],
         'commentCount' => $node[ 'comment_count' ],
         'status' => $node[ 'status' ],
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?nid=' . $nid . '',
      ];

      $posts = [ ];

      $authorPanelInfo = [
         'uid' => NULL,
         'username' => NULL,
         'avatar' => NULL,
         'sex' => NULL,
         'access_ip' => NULL,
         'join_time' => NULL,
         'points' => NULL,
      ];

      if ( $pageNo == 1 )
      { // show node details as the first post
         $node[ 'type' ] = 'node';
         $node[ 'createTime' ] = \date( 'm/d/Y H:i', $node[ 'create_time' ] );
         if ( $node[ 'lastModifiedTime' ] )
         {
            $node[ 'lastModifiedTime' ] = \date( 'm/d/Y H:i', $node[ 'last_modified_time' ] );
         }
         try
         {
            $node[ 'HTMLbody' ] = BBCode::parse( $node[ 'body' ] );
         }
         catch ( \Exception $e )
         {
            $node[ 'HTMLbody' ] = \nl2br( $node[ 'body' ] );
            $this->logger->error( $e->getMessage(), $e->getTrace() );
         }
         // $node['signature'] = \nl2br( $node['signature'] );
         $node[ 'authorPanel' ] = $this->_authorPanel( \array_intersect_key( $node, $authorPanelInfo ) );
         $node[ 'city' ] = $this->request->getCityFromIP( $node[ 'access_ip' ] );
         $node[ 'attachments' ] = $this->_attachments( $node[ 'files' ], $node[ 'body' ] );
         $node[ 'filesJSON' ] = \json_encode( $node[ 'files' ] );

         $posts[] = $node;
      }

      $nodeObj = new NodeObject();
      $comments = $nodeObj->getForumNodeComments( $nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      if ( \sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c[ 'type' ] = 'comment';
            $c[ 'createTime' ] = \date( 'm/d/Y H:i', $c[ 'create_time' ] );
            if ( $c[ 'lastModifiedTime' ] )
            {
               $c[ 'lastModifiedTime' ] = \date( 'm/d/Y H:i', $c[ 'last_modified_time' ] );
            }

            try
            {
               $c[ 'HTMLbody' ] = BBCode::parse( $c[ 'body' ] );
            }
            catch ( \Exception $e )
            {
               $c[ 'HTMLbody' ] = \nl2br( $c[ 'body' ] );
               $this->logger->error( $e->getMessage(), $e->getTrace() );
            }
            // $c['signature'] = \nl2br( $c['signature'] );
            $c[ 'authorPanel' ] = $this->_authorPanel( \array_intersect_key( $c, $authorPanelInfo ) );
            $c[ 'city' ] = $this->request->getCityFromIP( $c[ 'access_ip' ] );
            $c[ 'attachments' ] = $this->_attachments( $c[ 'files' ], $c[ 'body' ] );
            $c[ 'filesJSON' ] = \json_encode( $c[ 'files' ] );

            $posts[] = $c;
         }
      }

      $editor_contents = [
         'title' => $node[ 'title' ],
         'form_handler' => '/node/' . $nid . '/comment',
         'hasFile' => TRUE
      ];
      $editor = new Template( 'editor_bbcode', $editor_contents );

      $contents += [
         'posts' => $posts,
         'editor' => $editor
      ];

      $this->html->var[ 'content' ] = new Template( 'node_forum_topic', $contents );
   }

   private function _authorPanel( $info )
   {
      static $authorPanels = [ ];

      if ( !(\array_key_exists( 'uid', $info ) && $info[ 'uid' ] > 0) )
      {
         return NULL;
      }

      if ( !\array_key_exists( $info[ 'uid' ], $authorPanels ) )
      {
         $authorPanelCache = $this->_getIndependentCache( 'ap' . $info[ 'uid' ] );
         $authorPanel = $authorPanelCache->fetch();
         if ( !$authorPanel )
         {
            $info[ 'joinTime' ] = \date( 'm/d/Y', $info[ 'join_time' ] );
            $info[ 'sex' ] = isset( $info[ 'sex' ] ) ? ($info[ 'sex' ] == 1 ? '男' : '女') : '未知';
            if ( empty( $info[ 'avatar' ] ) )
            {
               $info[ 'avatar' ] = '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg';
            }
            $info[ 'city' ] = $this->request->getCityFromIP( $info[ 'access_ip' ] );
            $authorPanel = new Template( 'author_panel_forum', $info );
            $authorPanelCache->store( $authorPanel );
         }
         $authorPanels[ $info[ 'uid' ] ] = $authorPanel;
      }

      return $authorPanels[ $info[ 'uid' ] ];
   }

   private function _attachments( $files, $body )
   {
      $attachments = NULL;
      $_files = [ ];
      $_images = [ ];

      foreach ( $files as $f )
      {
         $tmp = \explode( '.', $f[ 'path' ] );
         $type = \array_pop( $tmp );
         switch ( $type )
         {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
               $isImage = TRUE;
               $bbcode = '[img]' . $f[ 'path' ] . '[/img]';
               break;
            default :
               $isImage = FALSE;
               $bbcode = '[file="' . $f[ 'path' ] . '"]' . $f[ 'name' ] . '[/file]';
         }

         if ( \strpos( $body, $bbcode ) !== FALSE )
         {
            continue;
         }

         if ( $isImage )
         {
            $_images[] = new HTMLElement( 'figure', [
               new HTMLElement( 'figcaption', $f[ 'name' ] ),
               new HTMLElement( 'img', NULL, ['src' => $f[ 'path' ], 'alt' => '图片加载失败 : ' . $f[ 'name' ] ] ) ] );
         }
         else
         {
            $_files[] = $this->html->link( $f[ 'name' ], $f[ 'path' ] );
         }
      }

      if ( \sizeof( $_images ) > 0 )
      {
         $attachments .= new HTMLElement( 'div', $_images, ['class' => 'attach_images' ] );
      }
      if ( \sizeof( $_files ) > 0 )
      {
         $attachments .= new HTMLElement( 'div', $_files, ['class' => 'attach_files' ] );
      }

      return $attachments;
   }

   private function _displayYellowPage( $nid )
   {
      $nodeObj = new NodeObject();
      $node = $nodeObj->getYellowPageNode( $nid );
      $tags = $nodeObj->getTags( $nid );

      $this->html->var[ 'head_title' ] = $node[ 'title' ] . ' - ' . $this->html->var[ 'head_title' ];
      $this->html->var[ 'head_description' ] = $node[ 'title' ] . ', ' . $this->html->var[ 'head_description' ];

      if ( \is_null( $node ) )
      {
         $this->pageNotFound();
      }

      $breadcrumb = [ ];
      foreach ( $tags as $i => $t )
      {
         $breadcrumb[ $t[ 'name' ] ] = ($i === self::$_city->YPRootID ? '/yp' : ('/yp/' . $i));
      }
      $breadcrumb[ $node[ 'title' ] ] = NULL;

      list($pageNo, $pageCount) = $this->_getPagerInfo( $node[ 'comment_count' ], self::COMMENTS_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/node/' . $nid );

      $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1;

      $contents = [
         'nid' => $nid,
         'cid' => $tags[ 2 ][ 'cid' ],
         'commentCount' => $node[ 'comment_count' ],
         'status' => $node[ 'status' ],
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?nid=' . $nid . '',
      ];

      $node[ 'type' ] = 'node';

      if ( $pageNo == 1 )
      { // show node details as the first post
         try
         {
            $node[ 'HTMLbody' ] = BBCode::parse( $node[ 'body' ] );
         }
         catch ( \Exception $e )
         {
            $node[ 'HTMLbody' ] = \nl2br( $node[ 'body' ] );
            $this->logger->error( $e->getMessage(), $e->getTrace() );
         }
         $node[ 'attachments' ] = $this->_attachments( $node[ 'files' ], $node[ 'body' ] );
         //$node['filesJSON'] = \json_encode($node['files']);
      }

      $comments = $nodeObj->getYellowPageNodeComments( $nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      $cmts = [ ];
      if ( \sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c[ 'id' ] = $c[ 'id' ];
            $c[ 'type' ] = 'comment';
            $c[ 'createTime' ] = \date( 'm/d/Y H:i', $c[ 'create_time' ] );
            if ( $c[ 'lastModifiedTime' ] )
            {
               $c[ 'lastModifiedTime' ] = \date( 'm/d/Y H:i', $c[ 'last_modified_time' ] );
            }
            $c[ 'HTMLbody' ] = \nl2br( $c[ 'body' ] );
            try
            {
               $c[ 'HTMLbody' ] = BBCode::parse( $c[ 'body' ] );
            }
            catch ( \Exception $e )
            {
               $c[ 'HTMLbody' ] = \nl2br( $c[ 'body' ] );
               $this->logger->error( $e->getMessage(), $e->getTrace() );
            }

            $cmts[] = $c;
         }
      }

      $editor_contents = [
         'form_handler' => '/node/' . $nid . '/comment'
      ];
      $editor = new Template( 'editor_bbcode', $editor_contents );

      $contents += [
         'node' => $node,
         'comments' => $cmts,
         'editor' => $editor
      ];

      $this->html->var[ 'content' ] = new Template( 'node_yellow_page', $contents );
   }

}

//__END_OF_FILE__
