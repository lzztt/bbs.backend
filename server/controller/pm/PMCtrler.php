<?php

namespace site\controller\pm;

use site\controller\PM;
use site\controller\User as UserController;
use site\dbobject\PrivMsg;
use site\dbobject\User as UserObject;
use lzx\core\Mailer;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Hidden;
use lzx\html\TextArea;

class PMCtrler extends PM
{

   const PMS_PER_PAGE = 25;

   /**
    * default protected methods
    */
   protected function init()
   {
      parent::_init();
      // don't cache user page at page level
      $this->cache->setStatus( FALSE );

      if ( $this->request->uid == self::GUEST_UID )
      {
         $this->_dispayLogin( $this->request->uri );
      }
   }

   public function run()
   {
      $this->display();
   }

   public function display()
   {
      $topicID = (int) $this->args[ 0 ];

      $pm = new PrivMsg();
      $msgs = $pm->getPMConversation( $topicID, $this->request->uid );
      if ( \sizeof( $msgs ) == 0 )
      {
         $this->error( '错误：该条短信不存在。' );
         return;
      }

      $replyTo = $pm->getReplyTo( $topicID, $this->request->uid );

      $list = [ ];
      foreach ( $msgs as $m )
      {
         $avatar = new HTMLElement(
            'div', $this->html->link(
               new HTMLElement( 'img', NULL, $attr = [
               'alt' => $m[ 'username' ] . ' 的头像',
               'src' => $m[ 'avatar' ] ? $m[ 'avatar' ] : '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg',
               ] ), '/user/' . $m[ 'uid' ] ), ['class' => 'pm_avatar' ] );

         $info = new HTMLElement(
            'div', [$this->html->link( $m[ 'username' ], '/user/' . $m[ 'uid' ] ), '<br />', \date( 'm/d/Y', $m[ 'time' ] ), '<br />', \date( 'H:i', $m[ 'time' ] ) ], ['class' => 'pm_info' ] );

         $body = new HTMLElement(
            'div', \nl2br( $m[ 'body' ] ) . (new HTMLElement(
            'div', $this->html->link( ($m[ 'id' ] == $topicID ? '删除话题' : '删除' ), '/pm/delete/' . $topicID . '/' . $m[ 'id' ], ['class' => 'button' ] ), ['class' => 'ed_actions' ] )), ['class' => 'pm_body' ] );
         $list[] = $avatar . $info . $body;
      }

      $messages = $this->html->ulist( $list, ['class' => 'pm_thread' ] );

      $reply_form = new Form( array(
         'action' => '/pm/' . $topicID . '/reply',
         'id' => 'pm_reply'
         ) );
      $receipt = new Input( 'to', '收信人' );
      $receipt->attributes = ['readonly' => 'readonly' ];
      $receipt->setValue( $replyTo[ 'username' ] );
      $message = new TextArea( 'body', '回复内容', '最少5个字母或3个汉字', TRUE );
      $toUID = new Hidden( 'toUID', $replyTo[ 'id' ] );
      $fromUID = new Hidden( 'fromUID', $this->request->uid );

      $reply_form->setData( array(
         $receipt->toHTMLElement(),
         $message->toHTMLElement(),
         $fromUID->toHTMLElement(),
         $toUID->toHTMLElement()
      ) );
      $reply_form->setButton( array( 'submit' => '发送' ) );

      $this->html->var[ 'content' ] = $link_tabs . $pager . $messages . $reply_form;
   }

   public function reply()
   {
      $topicID = (int) $this->args[ 0 ];

      if ( $this->request->uid != $this->request->post[ 'fromUID' ] )
      {
         $this->error( '错误，用户没有权限回复此条短信' );
      }

      if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '错误：短信正文字数太少。' );
      }

      $user = new UserObject( $this->request->post[ 'toUID' ], 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '错误：收信人用户不存在。' );
      }

      $pm = new PrivMsg();
      $pm->topicID = $topicID;
      $pm->fromUID = $this->request->uid;
      $pm->toUID = $user->id;
      $pm->body = $this->request->post[ 'body' ];
      $pm->time = $this->request->timestamp;
      $pm->add();

      $mailer = new Mailer();
      $mailer->to = $user->email;
      $mailer->subject = $user->username . ' 您有一封新的站内短信';
      $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://www.houstonbbs.com/pm/' . $pm->topicID;
      if ( !$mailer->send() )
      {
         $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
      }

      $this->request->redirect( '/pm/' . $topicID );
   }

   public function delete()
   {
      $topicID = (int) $this->args[ 0 ];
      $messageID = (int) $this->args[ 1 ];

      $pm = new PrivMsg();
      $pm->id = $messageID;
      try
      {
         $pm->deleteByUser( $this->request->uid );
      }
      catch ( \Exception $e )
      {
         $this->error( 'failed to delete message ' . $messageID . ' as user ' . $this->request->uid );
      }

      $redirect_uri = $topicID == $messageID ? '/user/pm' : '/pm/' . $topicID;
      $this->request->redirect( $redirect_uri );
   }

}

//__END_OF_FILE__
