<?php

namespace site\controller\pm;

use site\controller\PM;
use site\dbobject\PrivMsg;
use lzx\html\HTMLElement;
use lzx\html\Form;
use lzx\html\Input;
use lzx\html\Hidden;
use lzx\html\TextArea;

class PMCtrler extends PM
{

   const PMS_PER_PAGE = 25;

   public function run()
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

}

//__END_OF_FILE__
