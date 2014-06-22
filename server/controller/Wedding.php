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

   protected function _init()
   {
      parent::_init();
      Template::$theme = $this->config->theme['wedding'];
   }

   protected function _final()
   {
      
   }

   protected function _ajax()
   {
      if ( $this->args[0] == 'checkin' && $this->request->get['id'] )
      {
         $a = new WeddingAttendee( $this->request->get['id'], NULL );
         $a->checkin = $this->request->timestamp;
         $a->update();
      }
      $this->request->pageExit();
   }

   public function _default()
   {
      $this->html->var['body'] = new Template( 'join_form' );
   }

   public function join()
   {
      $a = new WeddingAttendee();
      $a->name = $this->request->post['name'];
      $a->email = $this->request->post['email'];
      $a->phone = $this->request->post['phone'];
      $a->guests = $this->request->post['count'];
      $a->time = $this->request->timestamp;
      $a->status = 1;
      $a->add();

      $mailer = new Mailer( 'wedding' );
      $mailer->subject = 'wedding: ' . $a->name . ' ( ' . $a->guests . ' )';
      $mailer->body = (string) $a; //new Template('wedding_mail');
      $mailer->signature = '';
      $mailer->to = 'admin@houstonbbs.com';
      $mailer->send();

      $mailer->subject = '谢谢来参加我们的新婚答谢宴';
      $mailer->to = $a->email;
      $mailer->body = new Template( 'mail/attendee', ['name' => $a->name] );
      $mailer->send();

      $this->html->var['body'] = '<div class="center">谢谢' . $a->name . '!</div>'
         . '<div class="down">新婚答谢宴将于6月28日晚上6点28分举行<br />地点等详情已经通过email发送到您的邮箱 ' . $a->email . '<br />请查收~</div>';
   }

   public function listall()
   {
      Template::$theme = $this->config->theme['wedding2'];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      // logged in      
      $this->html->var['navbar'] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      $this->html->var['body'] = new Template( 'attendees', ['attendees' => $a->getList(), 'total' => $a->getTotal()] );
   }

   public function checkin()
   {
      Template::$theme = $this->config->theme['wedding2'];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      $a = new WeddingAttendee();
      $this->html->var['body'] = new Template( 'checkin', ['attendees' => $a->getList( 'name,guests,checkin' )] );
   }

   public function edit()
   {
      Template::$theme = $this->config->theme['wedding2'];
      // login first
      if ( !$this->session->loginStatus )
      {
         $this->login();
         return;
      }

      $this->html->var['navbar'] = new Template( 'navbar' );
      $a = new WeddingAttendee();
      if ( $this->request->post )
      {
         // save changes for one guest
         foreach ( $this->request->post as $k => $v )
         {
            $a->$k = $v;
         }
         $a->update();
         $this->html->var['body'] = $a->name . '的更新信息已经被保存';
      }
      else
      {
         if ( $this->args && (int) $this->args[0] > 0 )
         {
            // edit one guest
            $a->id = $this->args[0];
            $this->html->var['body'] = new Template( 'edit', \array_pop( $a->getList() ) );
         }
         else
         {
            // all guests in a list;
            $this->html->var['body'] = new Template( 'edit_list', ['attendees' => $a->getList( 'name' )] );
         }
      }
   }

   public function login()
   {
      Template::$theme = $this->config->theme['wedding2'];
      
      $defaultRedirect = '/wedding/listall';

      if ( $this->request->post )
      {
         if ( $this->request->post['password'] == 'alexmika' )
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

      $this->html->var['body'] = new Template( 'login', ['uri' => $this->request->uri] );
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

}
