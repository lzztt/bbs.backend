<?php

namespace site\api;

use site\Service;
use site\dbobject\PrivMsg;
use site\dbobject\User;

class MessageAPI extends Service
{

   const TOPIC_PER_PAGE = 25;

   private static $_mailbox = ['inbox', 'sent' ];

   /**
    * get private messages in user's mailbox (inbox,sent)
    * uri: /api/message/<mailbox>
    *      /api/message/<mailbox>?p=<pageNo>
    * 
    * get private message
    * uri: /api/message/<mid>
    */
   public function get()
   {
      if ( !$this->request->uid || empty( $this->args ) )
      {
         $this->forbidden();
      }

      if ( \is_numeric( $this->args[ 0 ] ) )
      {
         $return = $this->_getMessage( (int) $this->args[ 0 ] );
      }
      else
      {
         $return = $this->_getMessageList( $this->args[ 0 ] );
      }

      $this->_json( $return );
   }

   /**
    * send a private message to user
    * uri: /api/message?action=post
    * post: toUID=<toUID>&body=<body>(&topicMID=<topicMID>)
    */
   public function post()
   {
      if ( !$this->request->uid )
      {
         $this->error( '您必须先登录，才能发送站内短信' );
      }

      $topicMID = NULL;
      if ( \array_key_exists( 'topicMID', $this->request->post ) )
      {
         $topicMID = (int) $this->request->post[ 'topicMID' ];
      }

      $toUID = NULL;
      if ( $topicMID )
      {
         // reply an existing message topic
         $toUID = $pm->getReplyTo( $topicID, $this->request->uid );
         if ( !$toUID )
         {
            $this->error( '短信不存在，未找到短信收信人' );
         }
      }
      else
      {
         // send a new message topic
         $toUID = (int) $this->request->post[ 'toUID' ];
         if ( $toUID )
         {
            if ( $toUID == $this->request->uid )
            {
               $this->error( '不能给自己发送站内短信' );
            }
         }
         else
         {
            $this->error( '收信人用户不存在' );
         }
      }

      $user = new User( $toUID, 'username,email' );

      if ( !$user->exists() )
      {
         $this->error( '收信人用户不存在' );
      }

      // save pm to database
      if ( \strlen( $this->request->post[ 'body' ] ) < 5 )
      {
         $this->error( '短信正文需最少5个字母或3个汉字' );
      }



      $pm = new PrivMsg();
      $pm->fromUID = $this->request->uid;
      $pm->toUID = $user->id;
      $pm->body = $this->request->post[ 'body' ];
      $pm->time = $this->request->timestamp;
      if ( $topicMID )
      {
         // reply an existing message topic
         $pm->msgID = $topicMID;
         $pm->add();
      }
      else
      {
         // start a new message topic
         $pm->add();
         $pm->msgID = $pm->id;
         $pm->update( 'msgID' );
      }


      $mailer = new Mailer();
      $mailer->to = $user->email;
      $mailer->subject = $user->username . ' 您有一封新的站内短信';
      $mailer->body = $user->username . ' 您有一封新的站内短信' . "\n" . '请登录后点击下面链接阅读' . "\n" . 'http://' . $this->request->domain . '/pm/' . $pm->msgID;
      if ( !$mailer->send() )
      {
         $this->logger->error( 'PM EMAIL REMINDER SENDING ERROR: ' . $pm->id );
      }

      $this->_json( ['body' => '您的短信已经发送给用户 <i>' . $user->username . '</i>' ] );
   }

   /**
    * delete a private message from user's message box
    * uri: /api/message/<mid>?action=delete
    */
   public function delete()
   {
      if ( !$this->request->uid || empty( $this->args ) )
      {
         $this->forbidden();
      }

      $mids = [ ];

      foreach ( \explode( ',', $this->args[ 0 ] ) as $mid )
      {
         if ( \is_numeric( $mid ) && \intval( $mid ) > 0 )
         {
            $mids[] = (int) $mid;
         }
      }

      $error = [ ];
      foreach ( $mids as $mid )
      {
         $pm = new PrivMsg( $mid, NULL );
         try
         {
            $pm->deleteByUser( $this->request->uid );
         }
         catch ( \Exception $e )
         {
            $this->logger->error( 'failed to delete message ' . $mid . ' as user ' . $this->request->uid );
            $error[] = 'failed to delete message ' . $mid;
         }
      }

      $this->_json( $error ? ['error' => $error ] : NULL  );
   }

   private function _getMessage( $mid )
   {
      if ( $mid > 0 )
      {
         $pm = new PrivMsg();
         $msgs = $pm->getPMConversation( $mid, $this->request->uid );
         if ( empty( $msgs ) )
         {
            $this->error( '错误：该条短信不存在。' );
         }

         foreach ( $msgs as $i => $m )
         {
            if ( empty( $m[ 'avatar' ] ) )
            {
               $msgs[ $i ][ 'avatar' ] = '/data/avatars/avatar0' . \mt_rand( 1, 5 ) . '.jpg';
            }
         }

         return $msgs;
      }
      else
      {
         $this->error( 'message does not exist' );
      }
   }

   private function _getMessageList( $mailbox )
   {
      $user = new User( $this->request->uid, NULL );
      if ( !\in_array( $mailbox, self::$_mailbox ) )
      {
         $this->error( 'mailbox does not exist: ' . $mailbox );
      }

      $pmCount = $user->getPrivMsgsCount( $mailbox );
      if ( $pmCount == 0 )
      {
         $this->error( $mailbox == 'sent' ? '您的发件箱里还没有短信。' : '您的收件箱里还没有短信。'  );
      }

      list($pageNo, $pageCount) = $this->_getPagerInfo( $pmCount, self::TOPIC_PER_PAGE );
      $msgs = $user->getPrivMsgs( $mailbox, self::TOPIC_PER_PAGE, ($pageNo - 1) * self::TOPIC_PER_PAGE );

      return [ 'msgs' => $msgs, 'pager' => ['pageNo' => $pageNo, 'pageCount' => $pageCount ] ];
   }

}

//__END_OF_FILE__
