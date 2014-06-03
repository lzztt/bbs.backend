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
      $mailer->subject = "Thanks you for comming";
      $mailer->body = "testsss ";
      $mailer->signature = '\n\nLongzhang & Ying';
      $mailer->to = $a->email;
      $mailer->send();
      
      $this->html->var['body'] = 'Thank you!';
   }

   public function listall()
   {
      $a = new WeddingAttendee();
      $l = $a->getList();
      $this->html->var['body'] = new Template( 'attendees', ['attendees' => $l] );
   }

}
