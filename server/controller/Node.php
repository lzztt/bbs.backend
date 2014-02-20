<?php

namespace site\controller;

use site\Controller;
use lzx\core\BBCode;
use lzx\html\HTMLElement;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\Node as NodeObject;
use site\dbobject\NodeYellowPage;
use site\dbobject\Comment;
use site\dbobject\Image;
use site\dbobject\User;
use site\dbobject\Activity;
use site\dbobject\Tag;

class Node extends Controller
{

   const COMMENTS_PER_PAGE = 10;

   public function run()
   {
      parent::run();

      $nid = \is_numeric( $this->request->args[1] ) ? \intval( $this->request->args[1] ) : 0;
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

      $rootTagID = \array_shift( \array_keys( $tags ) );

      $types = [
         Tag::FORUM_ID => 'ForumTopic',
         Tag::YP_ID => 'YellowPage',
      ];

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

      $this->runAction( $action );
   }

   public function ajax()
   {
      // url = /node/ajax/viewcount?nid=<nid>

      $viewCount = [];
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

   public function tagForumTopicAction()
   {
      $nid = \intval( $this->request->args[1] );
      $newTagID = \intval( $this->request->args[3] );

      $nodeObj = new NodeObject( $nid, 'uid,tid' );
      if ( $this->request->uid == 1 || $this->request->uid = $nodeObj->uid )
      {
         $oldTagID = $nodeObj->tid;
         $nodeObj->tid = $newTagID;
         $nodeObj->update( 'tid' );

         $this->cache->delete( '/forum/' . $oldTagID );
         $this->cache->delete( '/forum/' . $newTagID );
         $this->cache->delete( '/node/' . $nid );

         $this->request->redirect( '/node/' . $nid );
      }
      else
      {
         $this->logger->warn( 'wrong action : uid = ' . $this->request->uid );
         $this->request->pageForbidden();
      }
   }

   public function displayForumTopicAction()
   {
      $nid = \intval( $this->request->args[1] );
      $nodeObj = new NodeObject();
      $node = $nodeObj->getForumNode( $nid );
      $tags = $nodeObj->getTags( $nid );

      $this->html->var['head_title'] = $node['title'] . ' - ' . $this->html->var['head_title'];
      $this->html->var['head_description'] = $node['title'] . ', ' . $this->html->var['head_description'];

      if ( \is_null( $node ) )
      {
         $this->request->pageNotFound();
      }

      $breadcrumb = [];
      foreach ( $tags as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
            'title' => $t['description'],
            'name' => $t['name']
         ];
      }
      $breadcrumb[] = ['name' => $node['title']];

      list($pageNo, $pageCount) = $this->getPagerInfo( $node['comment_count'], self::COMMENTS_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/node/' . $node['id'] );

      $postNumStart = ($pageNo > 1) ? ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1 : 0; // first page start from the node and followed by comments

      $contents = [
         'nid' => $nid,
         'tid' => $node['tid'],
         'commentCount' => $node['comment_count'],
         'status' => $node['status'],
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?type=json&nid=' . $nid . '&nosession',
      ];

      $posts = [];

      $authorPanelInfo = [
         'uid' => NULL,
         'username' => NULL,
         'avatar' => NULL,
         'sex' => NULL,
         'access_ip' => NULL,
         'join_time' => NULL,
         'points' => NULL,
      ];


      $timeFormat = 'l, m/d/Y - H:i T';
      if ( $pageNo == 1 )
      { // show node details as the first post
         $node['type'] = 'node';
         $node['createTime'] = \date( $timeFormat, $node['create_time'] );
         if ( $node['lastModifiedTime'] )
         {
            $node['lastModifiedTime'] = \date( $timeFormat, $node['last_modified_time'] );
         }
         try
         {
            $node['HTMLbody'] = BBCode::parse( $node['body'] );
         }
         catch ( \Exception $e )
         {
            $node['HTMLbody'] = \nl2br( $node['body'] );
            $this->logger->error( $e->getMessage(), $e->getTrace() );
         }
         $node['signature'] = \nl2br( $node['signature'] );

         $node['authorPanel'] = $this->authorPanel( \array_intersect_key( $node, $authorPanelInfo ) );
         $node['city'] = $this->request->getCityFromIP( $node['access_ip'] );
         $node['attachments'] = $this->attachments( $node['files'], $node['body'] );
         $node['filesJSON'] = \json_encode( $node['files'] );

         $posts[] = $node;
      }

      $nodeObj = new NodeObject();
      $comments = $nodeObj->getForumNodeComments( $nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      if ( \sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c['type'] = 'comment';
            $c['createTime'] = \date( $timeFormat, $c['create_time'] );
            if ( $c['lastModifiedTime'] )
            {
               $c['lastModifiedTime'] = \date( $timeFormat, $c['last_modified_time'] );
            }

            try
            {
               $c['HTMLbody'] = BBCode::parse( $c['body'] );
            }
            catch ( \Exception $e )
            {
               $c['HTMLbody'] = \nl2br( $c['body'] );
               $this->logger->error( $e->getMessage(), $e->getTrace() );
            }
            $c['signature'] = \nl2br( $c['signature'] );

            $c['authorPanel'] = $this->authorPanel( \array_intersect_key( $c, $authorPanelInfo ) );
            $c['city'] = $this->request->getCityFromIP( $c['access_ip'] );
            $c['attachments'] = $this->attachments( $c['files'], $c['body'] );
            $c['filesJSON'] = \json_encode( $c['files'] );

            $posts[] = $c;
         }
      }

      $editor_contents = [
         'show_title' => FALSE,
         'title' => $node['title'],
         'form_handler' => '/node/' . $nid . '/comment'
      ];
      $editor = new Template( 'editor_bbcode', $editor_contents );


      $contents += [
         'posts' => $posts,
         'editor' => $editor
      ];

      $this->html->var['content'] = new Template( 'node_forum_topic', $contents );
   }

   private function authorPanel( $info )
   {
      static $authorPanels = [];

      if ( !(\array_key_exists( 'uid', $info ) && $info['uid'] > 0) )
      {
         return NULL;
      }

      if ( !\array_key_exists( $info['uid'], $authorPanels ) )
      {
         $authorPanel = $this->cache->fetch( 'authorPanel' . $info['uid'] );
         if ( $authorPanel === FALSE )
         {
            $info['joinTime'] = date( 'm/d/Y', $info['join_time'] );
            $info['sex'] = isset( $info['sex'] ) ? ($info['sex'] == 1 ? '男' : '女') : '未知';
            if ( empty( $info['avatar'] ) )
            {
               $info['avatar'] = '/data/avatars/avatar0' . mt_rand( 1, 5 ) . '.jpg';
            }
            $info['city'] = $this->request->getCityFromIP( $info['access_ip'] );
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
      $_files = [];
      $_images = [];

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
            $img .= new HTMLElement( 'img', NULL, ['src' => $f['path'], 'alt' => '图片加载失败 : ' . $f['name']] );
            $_images[] = $img;
         }
         else
         {
            $_files[] = $this->html->link( $f['name'], $f['path'] );
         }
      }

      if ( \sizeof( $_images ) > 0 )
      {
         $attachments .= $this->html->ulist( $_images, ['class' => 'attach_images'], FALSE );
      }
      if ( \sizeof( $_files ) > 0 )
      {
         $attachments .= $this->html->olist( $_files, ['class' => 'attach_files'] );
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

      $files = \is_array( $this->request->post['files'] ) ? $this->request->post['files'] : [];
      $file = new Image();
      $file->updateFileList( $files, $this->config->path['file'], $nid );
      $this->cache->delete( 'imageSlider' );

      $this->cache->delete( '/node/' . $nid );

      $this->request->redirect( $this->request->referer );
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

      $activity = new Activity( $nid, 'nid' );
      if ( $activity->exists() )
      {
         $activity->delete();
      }

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

      $user = new User( $this->request->uid, 'createTime,points,status' );
      try
      {
         $user->validatePost( $this->request->ip, $this->request->timestamp );
         $comment = new Comment();
         $comment->nid = $nid;
         $comment->uid = $this->request->uid;
         $comment->body = $this->request->post['body'];
         $comment->createTime = $this->request->timestamp;
         $comment->add();
      }
      catch ( \Exception $e )
      {
         $this->logger->error( ' --comment-- ' . $comment->body );
         $this->error( $e->getMessage(), TRUE );
      }

      if ( $this->request->post['files'] )
      {
         $file = new Image();
         $file->updateFileList( $this->request->post['files'], $this->config->path['file'], $nid, $comment->id );
         $this->cache->delete( 'imageSlider' );
      }

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
      $redirect_uri = '/node/' . $nid . '?page=last#comment' . $comment->id;
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

      $breadcrumb = [];
      foreach ( $tags as $i => $t )
      {
         $breadcrumb[] = [
            'href' => ($i === Tag::YP_ID ? '/yp' : ('/yp/' . $i)),
            'title' => $t['description'],
            'name' => $t['name']
         ];
      }
      $breadcrumb[] = ['name' => $node['title']];

      list($pageNo, $pageCount) = $this->getPagerInfo( $node['comment_count'], self::COMMENTS_PER_PAGE );
      $pager = $this->html->pager( $pageNo, $pageCount, '/node/' . $nid );

      $postNumStart = ($pageNo - 1) * self::COMMENTS_PER_PAGE + 1;

      $contents = [
         'nid' => $nid,
         'cid' => $tags[2]['cid'],
         'commentCount' => $node['comment_count'],
         'status' => $node['status'],
         'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
         'pager' => $pager,
         'postNumStart' => $postNumStart,
         'ajaxURI' => '/node/ajax/viewcount?type=json&nid=' . $nid . '&nosession',
      ];

      $node['type'] = 'node';

      if ( $pageNo == 1 )
      { // show node details as the first post
         try
         {
            $node['HTMLbody'] = BBCode::parse( $node['body'] );
         }
         catch ( \Exception $e )
         {
            $node['HTMLbody'] = \nl2br( $node['body'] );
            $this->logger->error( $e->getMessage(), $e->getTrace() );
         }
         $node['attachments'] = $this->attachments( $node['files'], $node['body'] );
         //$node['filesJSON'] = \json_encode($node['files']);
      }

      $comments = $nodeObj->getYellowPageNodeComments( $nid, self::COMMENTS_PER_PAGE, ($pageNo - 1) * self::COMMENTS_PER_PAGE );

      $cmts = [];
      if ( sizeof( $comments ) > 0 )
      {
         foreach ( $comments as $c )
         {
            $c['id'] = $c['id'];
            $c['type'] = 'comment';
            $c['createTime'] = \date( 'm/d/Y H:i', $c['create_time'] );
            if ( $c['lastModifiedTime'] )
            {
               $c['lastModifiedTime'] = \date( 'm/d/Y H:i', $c['last_modified_time'] );
            }
            $c['HTMLbody'] = \nl2br( $c['body'] );

            $cmts[] = $c;
         }
      }

      $contents += [
         'node' => $node,
         'comments' => $cmts
      ];

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
         $node = new NodeObject( $nid, 'tid' );
         if ( $node->exists() )
         {
            $node->title = $this->request->post['title'];
            $node->body = $this->request->post['body'];
            $node->lastModifiedTime = $this->request->timestamp;
            $node->update();

            $node_yp = new NodeYellowPage( $nid, 'nid' );
            $keys = ['address', 'phone', 'email', 'website', 'fax'];
            foreach ( $keys as $k )
            {
               $node_yp->$k = \strlen( $this->request->post[$k] ) ? $this->request->post[$k] : NULL;
               ;
            }

            $node_yp->update();
         }

         $files = \is_array( $this->request->post['files'] ) ? $this->request->post['files'] : [];
         $file = new Image();
         $file->updateFileList( $files, $this->config->path['file'], $nid );

         $this->cache->delete( '/node/' . $nid );
         $this->cache->delete( '/yp/' . $node->tid );

         $this->request->redirect( '/node/' . $nid );
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

      $user = new User( $this->request->uid, 'createTime,points,status' );
      try
      {
         $user->validatePost( $this->request->ip, $this->request->timestamp );
         $comment = new Comment();
         $comment->nid = $nid;
         $comment->uid = $this->request->uid;
         $comment->body = $this->request->post['body'];
         $comment->createTime = $this->request->timestamp;
         $comment->add();
      }
      catch ( \Exception $e )
      {
         $this->logger->error( ' --comment-- ' . $comment->body );
         $this->error( $e->getMessage(), TRUE );
      }

      if ( isset( $this->request->post['star'] ) && \is_numeric( $this->request->post['star'] ) )
      {
         $rating = (int) $this->request->post['star'];
         if ( $rating > 0 )
         {
            $node->updateRating( $nid, $this->request->uid, $rating, $this->request->timestamp );
         }
      }

      $user->points += 1;
      $user->update( 'points' );

      $this->cache->delete( '/node/' . $nid );
      $this->cache->delete( 'latestYellowPageReplies' );

      $redirect_uri = '/node/' . $nid . '?page=last#comment' . $comment->id;
      $this->request->redirect( $redirect_uri );
   }

   public function activityForumTopicAction()
   {

      $nid = \intval( $this->request->args[1] );
      $node = new NodeObject( $nid, 'tid,uid,title' );

      if ( !$node->exists() )
      {
         $this->request->pageNotFound();
      }

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
         $breadcrumb = [];
         foreach ( $tags as $i => $t )
         {
            $breadcrumb[] = [
               'href' => ($i === Tag::FORUM_ID ? '/forum' : ('/forum/' . $i)),
               'title' => $t['description'],
               'name' => $t['name']
            ];
         }
         $breadcrumb[] = ['name' => $node->title];

         $content = [
            'breadcrumb' => $this->html->breadcrumb( $breadcrumb ),
            'exampleDate' => $this->request->timestamp - ($this->request->timestamp % 3600) + 259200
         ];
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
            $mailer->subject = '新活动 ' . $nid . ' 长于一天 (请检查)';
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