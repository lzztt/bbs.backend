<?php

namespace site\controller;

use lzx\core\Controller;
use lzx\core\BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dataobject\Node as NodeObject;
use site\dataobject\NodeYellowPage;
use site\dataobject\Comment;
use site\dataobject\File;
use site\dataobject\User;
use site\dataobject\Activity;

class Node extends Controller
{

   const COMMENTS_PER_PAGE = 10;

   public function run()
   {
      $page = $this->loadController( 'Page' );
      $page->updateInfo();
      $this->checkAJAX();
      $page->setPage();

      $nid = is_numeric( $this->request->args[1] ) ? \intval( $this->request->args[1] ) : 0;
      if ( $nid <= 0 )
      {
         $this->request->pageNotFound();
      }

      $nodeObj = new NodeObject();
      $tags = $nodeObj->getTags( $nid );
      if ( empty( $tags ) )
      {
         $this->request->pageNotFound();
      }

      $rootTagID = $tags[0]['tid'];

//$tags[0]['cid'] values as the key:
      $types = array(
         '1' => 'ForumTopic',
         '2' => 'YellowPage',
         '3' => 'Article',
         '4' => 'Poll'
      );

      if ( !\array_key_exists( $rootTagID, $types ) )
      {
         $this->logger->error( 'wrong root tag : nid = ' . $nid );
         $this->error( 'wrong node type' );
      }

      $action = isset( $this->request->args[2] ) ? $this->request->args[2] : 'display';

      if ( $action !== 'display' && $this->request->uid == 0 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $action = $action . $types[$rootTagID];
// public function name based on node type and action
      try
      {
         $this->runAction( $action );
      }
      catch ( \Exception $e )
      {
         $this->logger->error( $e->getMessage() );
         $this->request->pageNotFound( $e->getMessage() );
      }
   }

   public function ajax()
   {
      // url = /node/ajax/viewcount?nid=<nid>

      $viewCount = array( );
      if ( $this->request->args[2] == 'viewcount' )
      {
         $nid = \intval( $this->request->get['nid'] );
         $nodeObj = new NodeObject( $nid, 'viewCount' );
         if ( $nodeObj->exists() )
         {
            $nodeObj->viewCount = $nodeObj->viewCount + 1;
            $nodeObj->update( 'viewCount' );
            $viewCount['viewCount_' . $nid] = $nodeObj->viewCount;
         }
      }

      return $viewCount;
   }

   public function displayForumTopicAction()
   {
      $nodeObj = new NodeObject();
      $nid = \intval( $this->request->args[1] );
      $node = $nodeObj->getForumNode( $nid );
      $tags = $nodeObj->getTags( $nid );

      $this->html->var['head_title'] = $node['title'] . ' - ' . $this->html->var['head_title'];
      $this->html->var['head_description'] = $node['title'] . ', ' . $this->html->var['head_description'];

      if ( \is_null( $node ) )
      {
         $this->request->pageNotFound();
      }

      $breadcrumb = '<a href="/forum">论坛</a>'
            . ' > <a href="/forum/' . $tags[1]['tid'] . '">' . $tags[1]['name'] . '</a>'
            . ' > <a href="/forum/' . $tags[2]['tid'] . '">' . $tags[2]['name'] . '</a>';

      $pageNo = $this->request->get['page'];
      $pageCount = ceil( $node['commentCount'] / self::COMMENTS_PER_PAGE );
      list($pageNo, $pager) = $this->html->generatePager( $pageNo, $pageCount, '/node/' . $node['nid'] );

      $postNumStart = ($pageNo > 1) ? ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1 : 0; // first page start from the node and followed by comments

      $contents = array(
         'nid' => $node['nid'],
         'tid' => $tags[2]['tid'],
         'title' => $node['title'],
         'commentCount' => $node['commentCount'],
         'status' => $node['status'],
         'breadcrumb' => $breadcrumb,
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?type=json&nid=' . $nid,
      );

      $posts = array( );

      $authorPanelInfo = array(
         'uid' => NULL,
         'username' => NULL,
         'avatar' => NULL,
         'sex' => NULL,
         'accessIP' => NULL,
         'joinTime' => NULL,
         'points' => NULL,
      );


      $timeFormat = 'l, m/d/Y - H:i T';
      if ( $pageNo == 1 )
      { // show node details as the first post
         $node['id'] = $node['nid'];
         $node['type'] = 'node';
         $node['createTime'] = \date( $timeFormat, $node['createTime'] );
         if ( $node['lastModifiedTime'] )
         {
            $node['lastModifiedTime'] = \date( $timeFormat, $node['lastModifiedTime'] );
         }
         $node['HTMLbody'] = BBCode::filter( $node['body'] );
         $node['signature'] = \nl2br( $node['signature'] );

         $node['authorPanel'] = $this->authorPanel( \array_intersect_key( $node, $authorPanelInfo ) );
         $node['city'] = $this->request->getCityFromIP( $node['accessIP'] );
         $node['attachments'] = $this->attachments( $node['files'], $node['body'] );
         $node['filesJSON'] = \json_encode( $node['files'] );

         $posts[] = $node;
      }

      $nodeObj = new NodeObject();
      $comments = $nodeObj->getForumNodeComments( $node['nid'], self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      if ( sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c['id'] = $c['cid'];
            $c['type'] = 'comment';
            $c['createTime'] = \date( $timeFormat, $c['createTime'] );
            if ( $c['lastModifiedTime'] )
            {
               $c['lastModifiedTime'] = \date( $timeFormat, $c['lastModifiedTime'] );
            }
            $c['HTMLbody'] = BBCode::filter( $c['body'] );
            $c['signature'] = \nl2br( $c['signature'] );

            $c['authorPanel'] = $this->authorPanel( \array_intersect_key( $c, $authorPanelInfo ) );
            $c['city'] = $this->request->getCityFromIP( $c['accessIP'] );
            $c['attachments'] = $this->attachments( $c['files'], $c['body'] );
            $c['filesJSON'] = \json_encode( $c['files'] );

            $posts[] = $c;
         }
      }

      $editor_contents = array(
         'show_title' => FALSE,
         'title' => $node['title'],
         'form_handler' => '/node/' . $node['nid'] . '/comment'
      );
      $editor = new Template( 'editor_bbcode', $editor_contents );


      $contents += array(
         'posts' => $posts,
         'editor' => $editor
      );

      $this->html->var['content'] = new Template( 'node_forum_topic', $contents );
   }

   private function authorPanel( $info )
   {
      static $authorPanels = array( );

      if ( !(\array_key_exists( 'uid', $info ) && $info['uid'] > 0) )
      {
         return NULL;
      }

      if ( !\array_key_exists( $info['uid'], $authorPanels ) )
      {
         $authorPanel = $this->cache->fetch( 'authorPanel' . $info['uid'] );
         if ( $authorPanel === FALSE )
         {
            $info['joinTime'] = date( 'm/d/Y', $info['joinTime'] );
            $info['sex'] = isset( $info['sex'] ) ? ($info['sex'] == 1 ? '男' : '女') : '未知';
            if ( empty( $info['avatar'] ) )
            {
               $info['avatar'] = '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg';
            }
            $info['city'] = $this->request->getCityFromIP( $info['accessIP'] );
            $authorPanel = new Template( 'author_panel_forum', $info );
            $this->cache->store( 'authorPanel' . $info['uid'], $authorPanel );
         }
         $authorPanels[$info['uid']] = $authorPanel;
      }

      return $authorPanels[$info['uid']];
   }

   private function attachments( $files, $body )
   {
      $attachments = NULL;
      $_files = array( );
      $_images = array( );

      foreach ( $files as $f )
      {
         $tmp = \explode( '.', $f['path'] );
         $type = \array_pop( $tmp );
         switch ( $type )
         {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
               $isImage = TRUE;
               $bbcode = '[img]' . $f['path'] . '[/img]';
               break;
            default :
               $isImage = FALSE;
               $bbcode = '[file="' . $f['path'] . '"]' . $f['name'] . '[/file]';
         }

         if ( \strpos( $body, $bbcode ) !== FALSE )
         {
            continue;
         }

         if ( $isImage )
         {
            $img = new HTMLElement( 'h4', $f['name'] );
            $img .= new HTMLElement( 'img', NULL, array( 'src' => $f['path'], 'alt' => '图片加载失败 : ' . $f['name'] ) );
            $_images[] = $img;
         }
         else
         {
            $_files[] = $this->html->link( $f['name'], $f['path'] );
         }
      }

      if ( sizeof( $_images ) > 0 )
      {
         $attachments .= $this->html->ulist( $_images, array( 'class' => 'attach_images' ), FALSE );
      }
      if ( sizeof( $_files ) > 0 )
      {
         $attachments .= $this->html->olist( $_files, array( 'class' => 'attach_files' ) );
      }

      return $attachments;
   }

   public function editForumTopicAction()
   { // edit existing comment
      $this->cache->setStatus( FALSE );

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'uid,status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( \strlen( $this->request->post['body'] ) < 5 || \strlen( $this->request->post['title'] ) < 5 )
      {
         $this->error( 'Topic title or body is too short.' );
      }

      if ( $this->request->uid != 1 && $this->request->uid != $node->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $node->title = $this->request->post['title'];
      $node->body = $this->request->post['body'];
      $node->lastModifiedTime = $this->request->timestamp;

      try
      {
         $node->update();
      }
      catch ( \Exception $e )
      {
         $this->error( $e->getMessage(), TRUE );
      }

      $files = \is_array( $this->request->post['files'] ) ? $this->request->post['files'] : array( );
      $file = new File();
      $file->updateFileList( $files, $nid );
      $this->cache->delete( 'imageSlider' );

      $this->cache->delete( '/node/' . $nid );

      $this->request->redirect();
// refresh node content cache
   }

   public function deleteForumTopicAction()
   {
      $this->cache->setStatus( FALSE );

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'uid,tid,status' );
      $tags = $node->getTags( $nid );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( $this->request->uid != 1 && $this->request->uid != $node->uid )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      $node->status = 0;
      $node->update( 'status' );

      $user = new User( $node->uid, 'points' );
      $user->points -= 3;
      $user->update( 'points' );

      $this->cache->delete( '/node/' . $nid );
      $this->cache->delete( '/forum/' . $node->tid );
      $this->request->redirect( '/forum/' . $node->tid );
   }

   public function commentForumTopicAction()
   { // create new comment
      $this->cache->setStatus( FALSE );

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'tid,status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( strlen( $this->request->post['body'] ) < 5 )
      {
         $this->error( '错误：评论正文字数太少。' );
      }

      $comment = new Comment();
      $comment->nid = $nid;
      $comment->uid = $this->request->uid;
      $comment->body = $this->request->post['body'];
      $comment->createTime = $this->request->timestamp;
      try
      {
         $comment->add();
      }
      catch ( \Exception $e )
      {
         $this->error( $e->getMessage(), TRUE );
      }

      if ( $this->request->post['files'] )
      {
         $file = new File();
         $file->updateFileList( $this->request->post['files'], $nid, $comment->cid );
         $this->cache->delete( 'imageSlider' );
      }

      $user = new User( $comment->uid, 'points' );
      $user->points += 1;
      $user->update( 'points' );

      $this->cache->delete( '/node/' . $nid );
      $this->cache->delete( '/forum/' . $node->tid );
      $this->cache->delete( 'latestForumTopicReplies' );
      if ( \in_array( $nid, $node->getHotForumTopicNIDs( $this->request->timestamp - 604800 ) ) )
      {
         $this->cache->delete( 'hotForumTopics' );
      }

      //$pageNoLast = ceil(($node->commentCount + 1) / self::COMMENTS_PER_PAGE);
      $redirect_uri = '/node/' . $nid . '?page=last#comment' . $comment->cid;
      $this->request->redirect( $redirect_uri );
   }

   public function displayYellowPageAction()
   {
      $nodeObj = new NodeObject();
      $nid = \intval( $this->request->args[1] );
      $node = $nodeObj->getYellowPageNode( $nid );
      $tags = $nodeObj->getTags( $nid );

      $this->html->var['head_title'] = $node['title'] . ' - ' . $this->html->var['head_title'];
      $this->html->var['head_description'] = $node['title'] . ', ' . $this->html->var['head_description'];

      if ( \is_null( $node ) )
      {
         $this->request->pageNotFound();
      }

      $breadcrumb = '<a href="/yp">黄页</a>'
            . ' > <a href="/yp/' . $tags[1]['tid'] . '">' . $tags[1]['name'] . '</a>'
            . ' > <a href="/yp/' . $tags[2]['tid'] . '">' . $tags[2]['name'] . '</a>';

      $pageNo = $this->request->get['page'];
      $pageCount = ceil( $node['commentCount'] / self::COMMENTS_PER_PAGE );
      list($pageNo, $pager) = $this->html->generatePager( $pageNo, $pageCount, '/node/' . $node['nid'] );

      $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1;

      $contents = array(
         'nid' => $node['nid'],
         'cid' => $tags[2]['cid'],
         'title' => $node['title'],
         'commentCount' => $node['commentCount'],
         /* 'viewCount' => $node['viewCount'], */
         'status' => $node['status'],
         'breadcrumb' => $breadcrumb,
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?type=json&nid=' . $nid,
      );


      $node['id'] = $node['nid'];
      $node['type'] = 'node';

      if ( $pageNo == 1 )
      { // show node details as the first post
         $node['HTMLbody'] = BBCode::filter( $node['body'] );
         $node['attachments'] = $this->attachments( $node['files'], $node['body'] );
         //$node['filesJSON'] = \json_encode($node['files']);
      }

      $nodeObj = new NodeObject();
      $comments = $nodeObj->getYellowPageNodeComments( $node['nid'], self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      $cmts = array( );
      if ( sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c['id'] = $c['cid'];
            $c['type'] = 'comment';
            $c['createTime'] = \date( 'm/d/Y H:i', $c['createTime'] );
            if ( $c['lastModifiedTime'] )
            {
               $c['lastModifiedTime'] = \date( 'm/d/Y H:i', $c['lastModifiedTime'] );
            }
            $c['HTMLbody'] = \nl2br( $c['body'] );

            $cmts[] = $c;
         }
      }

      $contents += array(
         'node' => $node,
         'comments' => $cmts
      );

      $this->html->var['content'] = new Template( 'node_yellow_page', $contents );
   }

   public function editYellowPageAction()
   {
      $this->cache->setStatus( FALSE );
      if ( $this->request->uid != 1 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }

      if ( empty( $this->request->post ) )
      {
         // display edit interface
         $nodeObj = new NodeObject();
         $nid = \intval( $this->request->args[1] );
         $contents = $nodeObj->getYellowPageNode( $nid );

         $this->html->var['content'] = new Template( 'editor_bbcode_yp', $contents );
      }
      else
      {
         // save modification
         $nid = \intval( $this->request->args[1] );
         $node = new NodeObject( $nid, 'nid,tid' );
         if ( $node->exists() )
         {
            $node->title = $this->request->post['title'];
            $node->body = $this->request->post['body'];
            $node->lastModifiedTime = $this->request->timestamp;
            $node->update();

            $node_yp = new NodeYellowPage( $nid, 'nid' );
            $keys = array( 'address', 'phone', 'email', 'website', 'fax' );
            foreach ( $keys as $k )
            {
               $node_yp->$k = \strlen( $this->request->post[$k] ) ? $this->request->post[$k] : NULL;
               ;
            }

            $node_yp->update();
         }

         $files = \is_array( $this->request->post['files'] ) ? $this->request->post['files'] : array( );
         $file = new File();
         $file->updateFileList( $files, $nid );

         $this->cache->delete( '/node/' . $node->nid );
         $this->cache->delete( '/yp/' . $node->tid );

         $this->request->redirect( '/node/' . $node->nid );
      }
// refresh node content cache
   }

   public function deleteYellowPageAction()
   {
      $this->cache->setStatus( FALSE );
      if ( $this->request->uid != 1 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'tid,status' );
      if ( $node->exists() && $node->status > 0 )
      {
         $node->status = 0;
         $node->update( 'status' );
      }

      $this->cache->delete( '/node/' . $nid );
      $this->request->redirect( '/yp/' . $node->tid );
   }

   public function commentYellowPageAction()
   { // create new comment
      $this->cache->setStatus( FALSE );

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'status' );

      if ( !$node->exists() || $node->status == 0 )
      {
         $this->error( 'node does not exist.' );
      }

      if ( strlen( $this->request->post['body'] ) < 5 )
      {
         $this->error( '错误：评论正文字数太少。' );
      }

      $comment = new Comment();
      $comment->nid = $nid;
      $comment->uid = $this->request->uid;
      $comment->body = $this->request->post['body'];
      $comment->createTime = $this->request->timestamp;
      $comment->add();

      if ( isset( $this->request->post['star'] ) && \is_numeric( $this->request->post['star'] ) )
      {
         $rating = (int) $this->request->post['star'];
         if ( $rating > 0 )
         {
            $node->updateRating( $nid, $this->request->uid, $rating, $this->request->timestamp );
         }
      }

      $user = new User( $comment->uid, 'points' );
      $user->points += 1;
      $user->update( 'points' );
      //$db->query('UPDATE users SET points = points + 1 WHERE uid = ' . $comment->uid);

      $this->cache->delete( '/node/' . $nid );
      $this->cache->delete( 'latestYellowPageReplies' );

      $redirect_uri = '/node/' . $nid . '?page=last#comment' . $comment->cid;
      $this->request->redirect( $redirect_uri );
   }

   public function activityForumTopicAction()
   {

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'tid,uid,title' );

      if ( $node->tid != 16 )
      {
         $this->error( '错误：错误的讨论区。' );
      }

      if ( $this->request->uid != $node->uid && $this->request->uid != 1 )
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->error( '错误：您只能将自己发表的帖子发布为活动。' );
      }

      if ( empty( $this->request->post ) )
      {
// display pm edit form
         $tags = $node->getTags( $nid );
         $breadcrumb = '<a href="/forum">论坛</a>'
               . ' > <a href="/forum/' . $tags[1]['cid'] . '">' . $tags[1]['name'] . '</a>'
               . ' > <a href="/forum/' . $tags[2]['cid'] . '">' . $tags[2]['name'] . '</a>'
               . ' > <a href="/node/' . $node->nid . '">' . $this->html->truncate( $node->title, 45 ) . '</a>';

         $content = array(
            'breadcrumb' => $breadcrumb,
            'exampleDate' => $this->request->timestamp - ($this->request->timestamp % 3600) + 259200
         );
         $this->html->var['content'] = new Template( 'activity_create', $content );
      }
      else
      {
         $this->cache->setStatus( FALSE );
         $startTime = strtotime( $this->request->post['start_time'] );
         $endTime = strtotime( $this->request->post['end_time'] );

         if ( $startTime < $this->request->timestamp || $endTime < $this->request->timestamp )
         {
            $this->error( '错误：活动开始时间或结束时间为过去的时间，不能发布为未来60天内的活动。' );
            return;
         }

         if ( $startTime > $this->request->timestamp + 5184000 || $endTime > $this->request->timestamp + 5184000 )
         {
            $this->error( '错误：活动开始时间或结束时间太久远，不能发布为未来60天内的活动。' );
            return;
         }

         if ( $startTime > $endTime )
         {
            $this->error( '错误：活动结束时间在开始时间之前，请重新填写时间。' );
            return;
         }

         if ( $endTime - $startTime > 86400 ) // 1 day
         {

            $mailer = new Mailer();
            $mailer->to = 'admin@houstonbbs.com';
            $mailer->subject = '新活动 ' . $node->nid . ' 长于一天 (请检查)';
            $mailer->body = $node->title . ' : ' . \date( 'm/d/Y H:i', $startTime ) . ' - ' . \date( 'm/d/Y H:i', $endTime );
            if ( $mailer->send() === FALSE )
            {
               $this->logger->info( 'sending long activity notice email error.' );
            }
         }

         $activity = new Activity();
         $activity->addActivity( $nid, $startTime, $endTime );

         $this->html->var['content'] = '您的活动申请已经提交并等待管理员激活，一般会在一小时之内被激活并且提交到首页，活动被激活后您将会收到电子邮件通知。';
      }
   }

}

//__END_OF_FILE__