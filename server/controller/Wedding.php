<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace site\controller;

use site\Controller;
use lzx\html\Template;
use lzx\core\Mailer;
use site\dbobject\Wedding as WeddingAttendee;

/**
 * Description of Wedding
 *
 * @author ikki
 */
class Wedding extends Controller
{

   private $_register_end = FALSE;

   protected function _init()
   {
      parent::_init();
      Template::$theme = $this->config->theme[ 'wedding' ];

      if ( $this->session->loginStatus !== TRUE && \file_exists( '/tmp/wedding' ) )
      {
         $this->_register_end = TRUE;
      }
   }

   protected function _final()
   {
      
   }

   protected function _ajax()
   {
      // disable checkin
      $this->request->pageForbidden();
      
      if ( $this->args[ 0 ] == 'checkin' && $this->request->get[ 'id' ] )
      {
         $a = new WeddingAttendee( $this->request->get[ 'id' ], NULL );
         $a->checkin = $this->request->timestamp;
         $a->update();
      }
      $this->request->pageExit();
   }

   public function _default()
   {
      $this->html->var[ 'body' ] = new Template( 'join_form' );
   }

   public function join()
   {
      $a = new WeddingAttendee();
      $a->name = $this->request->post[ 'name' ];
      $a->email = $this->request->post[ 'email' ];
      $a->phone = $this->request->post[ 'phone' ];
      $a->guests = $this->request->post[ 'count' ];
      $a->time = $this->request->timestamp;
      $a->status = 1;
      if ( $this->_register_end )
      {
         $days = \ceil( ( $this->request->timestamp - \strtotime( '2014/06/19' ) ) / 86400 );
      }
      else
      {
         $a->add();
      }

      $mailer = new Mailer( 'wedding' );
      $mailer->subject = 'wedding: ' . $a->name . ' ( ' . $a->guests . ' )';
      if ( $this->_register_end )
      {
         $mailer->subject = 'LATE ' . $days . ' DAYS : ' . $mailer->subject;
      }
      $mailer->body = (string) $a; //new Template('wedding_mail');
      $mailer->signature = '';
      $mailer->to = 'admin@houstonbbs.com';
      $mailer->send();

      if ( $this->_register_end )
      {
         $this->error( '迟到' . $days . '天的报名 :(<br />新婚答谢宴报名已于2014年6月18号(星期三)结束' );
      }

      $mailer->subject = '谢谢来参加我们的新婚答谢宴';
      $mailer->to = $a->email;
      $mailer->body = new Template( 'mail/attendee', ['name' => $a->name ] );
      $mailer->send();

      $this->html->var[ 'body' ] = '<div class="center">谢谢' . $a->name . '!</div>'
         . '<div class="down">新婚答谢宴将于6月28日晚上6点28分举行<br />地点等详情已经通过email发送到您的邮箱 ' . $a->email . '<br />请查收~</div>';
   }

   public function add()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      // logged in      
      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      if ( $this->request->post )
      {
         // save changes for one guest
         $a = new WeddingAttendee();

         foreach ( $this->request->post as $k => $v )
         {
            $a->$k = $v;
         }
         $a->time = $this->request->timestamp;
         $a->status = 1;
         $a->add();
         $this->html->var[ 'body' ] = $a->name . '已经被添加';
      }
      else
      {
         $this->html->var[ 'body' ] = new Template( 'join_form' );
      }
   }

   public function listall()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      // logged in      
      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      $a->where('tid', 0, '>');
      list($table_guests, $table_counts, $total) = $this->_getTableGuests( $a->getList('name,tid,guests,email,phone,time,checkin'), 'guests' );

      $this->html->var[ 'body' ] = new Template( 'attendees', ['tables' => $table_guests, 'counts' => $table_counts, 'total' => $total ] );
   }
   
   public function gift()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      // logged in      
      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      list($table_guests, $table_counts, $total) = $this->_getTableGuests( $a->getList('name,tid,gift,value,guests,comment'), 'value' );

      $this->html->var[ 'body' ] = new Template( 'gifts', ['tables' => $table_guests, 'counts' => $table_counts, 'total' => $total ] );
   }

   public function checkin()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      $a = new WeddingAttendee();
      $a->where('tid', 0, '>');
      list($table_guests, $table_counts, $total) = $this->_getTableGuests( $a->getList( 'name,guests,checkin,tid' ), 'guests' );
      $this->html->var[ 'body' ] = new Template( 'checkin', ['tables' => $table_guests ] );
   }

   public function edit()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      $this->html->var[ 'navbar' ] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      if ( $this->request->post )
      {
         // save changes for one guest
         foreach ( $this->request->post as $k => $v )
         {
            $a->$k = $v;
         }
         $a->update();
         $this->html->var[ 'body' ] = $a->name . '的更新信息已经被保存';
      }
      else
      {
         if ( $this->args && (int) $this->args[ 0 ] > 0 )
         {
            // edit one guest
            $a->id = $this->args[ 0 ];
            $this->html->var[ 'body' ] = new Template( 'edit', \array_pop( $a->getList() ) );
         }
         else
         {
            // all guests in a list;
            $a->order( 'tid' );
            $this->html->var[ 'body' ] = new Template( 'edit_list', ['attendees' => $a->getList( 'name' ) ] );
         }
      }
   }

   public function login()
   {
      Template::$theme = $this->config->theme[ 'wedding2' ];

      $defaultRedirect = '/wedding/listall';

      if ( $this->request->post )
      {
         if ( $this->request->post[ 'password' ] == 'alexmika928123' )
         {
            $this->session->loginStatus = TRUE;
            $uri = $this->session->loginRedirect;
            unset( $this->session->loginRedirect );
            $this->request->redirect( $uri ? $uri : $defaultRedirect  );
         }
      }
      else
      {
         if ( $this->request->referer && $this->request->referer !== '/wedding/login' )
         {
            $this->session->loginRedirect = $this->request->referer;
         }
         else
         {
            $this->session->loginRedirect = $defaultRedirect;
         }
      }

      $this->html->var[ 'body' ] = new Template( 'login', ['uri' => $this->request->uri ] );
   }

   public function logout()
   {
      $defaultRedirect = '/wedding/listall';

      unset( $this->session->loginStatus );
      if ( $this->request->referer && $this->request->referer !== '/wedding/logout' )
      {
         $uri = $this->request->referer;
      }
      else
      {
         $uri = $defaultRedirect;
      }
      $this->request->redirect( $uri );
   }

   protected function error( $msg )
   {
      $this->html->var[ 'body' ] = '<span style="color:blue;">错误 :</span> ' . $msg;
      $this->request->pageExit( (string) $this->html );
   }

   private function _getTableGuests( Array $guests, $countField )
   {
      $table_guests = [ ];
      $table_counts = [ ];
      $total = 0;
      foreach ( $guests as $g )
      {
         if ( !\array_key_exists( $g[ 'tid' ], $table_guests ) )
         {
            $table_guests[ $g[ 'tid' ] ] = [ ];
            $table_counts[ $g[ 'tid' ] ] = 0;
         }
         $table_guests[ $g[ 'tid' ] ][] = $g;
         $table_counts[ $g[ 'tid' ] ] += $g[ $countField ];
         $total += $g[ $countField ];
      }

      \ksort( $table_guests );

      return [$table_guests, $table_counts, $total ];
   }

}
