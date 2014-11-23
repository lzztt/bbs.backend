<?php

namespace site\controller;

use site\Controller;
use site\dbobject\User as UserObject;
use lzx\html\HTMLElement;

abstract class User extends Controller
{

   /**
    * protected methods
    */
   // switch to user or back to super user
   protected function _switchUser()
   {
      // switch to user from super user
      if ( $this->request->uid == self::UID_ADMIN )
      {
         if ( $this->id > 1 )
         {
            $user = new UserObject( $this->id, 'username' );
            if ( $user->exists() )
            {
               $this->logger->info( 'switching from user ' . $this->request->uid . ' to user ' . $user->id . '[' . $user->username . ']' );
               $this->session->suid = $this->request->uid;
               $this->session->setUserID( $user->id );
               $this->_var[ 'content' ] = 'switched to user [' . $user->username . '], use "logout" to switch back to super user';
            }
            else
            {
               $this->error( '错误：user does not exist' );
            }
         }
         else
         {
            $this->error( '错误：invalid user id' );
         }
      }
      // switch back to super user
      elseif ( isset( $this->session->suid ) )
      {
         if ( $this->session->suid == self::UID_ADMIN )
         {
            $user = new UserObject( $this->session->suid, 'username' );
            $this->logger->info( 'switching back from user ' . $this->request->uid . ' to user ' . $user->username );
            $this->session->setUserID( $user->id );
            $this->_var[ 'content' ] = 'not logged out, just switched back to super user';
         }
         unset( $this->session->suid );
      }
      // hide from normal user
      else
      {
         $this->pageNotFound();
      }
   }

   protected function _isBot( $m )
   {
      $try1 = unserialize( $this->request->curlGetData( 'http://www.stopforumspam.com/api?f=serial&email=' . $m ) );
      if ( $try1[ 'email' ][ 'appears' ] == 1 )
      {
         return TRUE;
      }
      $try2 = $this->request->curlGetData( 'http://botscout.com/test/?mail=' . $m );
      if ( $try2[ 0 ] == 'Y' )
      {
         return TRUE;
      }
      return FALSE;
   }

   protected function _recentTopics( $uid )
   {
      $user = new UserObject( $uid, NULL );

      if ( $uid == 1 && $this->request->uid != 1 )
      {
         $this->pageForbidden();
      }

      $posts = $user->getRecentNodes( 10 );

      $caption = '最近发表的论坛话题';
      $thead = ['cells' => ['论坛话题', '发表时间' ] ];
      $tbody = [ ];
      foreach ( $posts as $n )
      {
         $tbody[] = ['cells' => [Template::link( $n[ 'title' ], '/node/' . $n[ 'nid' ] ), \date( 'm/d/Y H:i', $n[ 'create_time' ] ) ] ];
      }

      $recent_topics = Template::table( ['caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ] );

      $posts = $user->getRecentComments( 10 );

      $caption = '最近回复的论坛话题';
      $thead = ['cells' => ['论坛话题', '回复时间' ] ];
      $tbody = [ ];
      foreach ( $posts as $n )
      {
         $tbody[] = ['cells' => [Template::link( $n[ 'title' ], '/node/' . $n[ 'nid' ] ), \date( 'm/d/Y H:i', $n[ 'create_time' ] ) ] ];
      }

      $recent_comments = Template::table( ['caption' => $caption, 'thead' => $thead, 'tbody' => $tbody ] );

      return new HTMLElement( 'div', [$recent_topics, $recent_comments ], ['class' => 'user_recent_topics' ] );
   }

}

//__END_OF_FILE__
